<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ValidIdFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

// One-time move of valid IDs from the public disk (reachable at /storage/valid_ids/...)
// to the private disk (storage/app/valid_ids). Safe to run more than once.
//
//   php artisan valid-ids:make-private                     # dry run: only shows what it would do
//   php artisan valid-ids:make-private --go                # move the files users point to
//   php artisan valid-ids:make-private --delete-orphans    # delete ID files no user points to
//
// Each file is copied, the copy is checked (exists + same size), and only then is the
// public original removed. users.valid_id doesn't change (same path on both disks).
class MakeValidIdsPrivate extends Command
{
    protected $signature = 'valid-ids:make-private
        {--go : Move the files (without this it is a dry run)}
        {--delete-orphans : Delete ID files that no user points to}';

    protected $description = 'Move valid IDs from public to private storage (dry run unless --go)';

    public function handle(): int
    {
        $public  = Storage::disk(ValidIdFiles::LEGACY_DISK);
        $private = Storage::disk(ValidIdFiles::DISK);
        $dir     = ValidIdFiles::DIR;

        $referenced = User::whereNotNull('valid_id')->where('valid_id', '!=', '')
            ->pluck('valid_id')->unique()->values();
        $refSet = array_flip($referenced->all());

        $publicFiles  = collect($public->files($dir));
        $privateFiles = collect($private->files($dir));

        $toMove = $referenced->filter(fn ($p) => $public->exists($p) && !$private->exists($p))->values();
        $already = $referenced->filter(fn ($p) => $private->exists($p))->values();
        $both = $already->filter(fn ($p) => $public->exists($p))->values(); // copied before, public copy left
        $missing = $referenced->filter(fn ($p) => !$public->exists($p) && !$private->exists($p))->values();
        $orphans = $publicFiles->map(fn ($p) => ['public', $p])
            ->merge($privateFiles->map(fn ($p) => ['private', $p]))
            ->filter(fn ($o) => !isset($refSet[$o[1]]))
            ->values();

        $dry = !$this->option('go') && !$this->option('delete-orphans');
        $this->line($dry ? '<comment>DRY RUN: nothing will be changed.</comment>' : '<info>Running.</info>');
        $this->newLine();
        $this->table(['', 'count'], [
            ['Users with a valid ID', $referenced->count()],
            ['To move (public -> private)', $toMove->count()],
            ['Already private', $already->count()],
            ['  ...of which a public copy is still left (will be removed)', $both->count()],
            ['Missing on both disks (user points to a file that does not exist)', $missing->count()],
            ['Orphan files (no user points to them)', $orphans->count()],
        ]);

        if ($missing->isNotEmpty()) {
            $this->warn('Missing files (first 20):');
            $missing->take(20)->each(fn ($p) => $this->line("  {$p}"));
        }
        if ($orphans->isNotEmpty()) {
            $this->warn('Orphan files (first 50):');
            $orphans->take(50)->each(fn ($o) => $this->line("  [{$o[0]}] {$o[1]}"));
        }

        if ($dry) {
            $this->newLine();
            $this->line('Run with --go to move the files, and/or --delete-orphans to delete the orphans.');
            return self::SUCCESS;
        }

        if ($this->option('go')) {
            $moved = 0; $failed = 0;
            foreach ($toMove->merge($both) as $path) {
                try {
                    if (!$private->exists($path)) {
                        $private->writeStream($path, $public->readStream($path));
                    }
                    if ($private->exists($path) && $private->size($path) === $public->size($path)) {
                        $public->delete($path);
                        $moved++;
                    } else {
                        $failed++;
                        $this->error("  Copy check failed, public file kept: {$path}");
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error("  Failed: {$path} ({$e->getMessage()})");
                }
            }
            $this->info("Moved {$moved} file(s) to private storage." . ($failed ? " {$failed} failed (kept on public)." : ''));
        }

        if ($this->option('delete-orphans')) {
            $deleted = 0;
            foreach ($orphans as [$where, $path]) {
                ($where === 'public' ? $public : $private)->delete($path);
                $deleted++;
            }
            $this->info("Deleted {$deleted} orphan file(s).");
        }

        $left = collect($public->files($dir))->filter(fn ($p) => isset($refSet[$p]))->count();
        $this->info($left === 0
            ? 'No valid ID that a user points to is left on the public disk.'
            : "{$left} valid ID(s) still on the public disk (see errors above).");

        return self::SUCCESS;
    }
}
