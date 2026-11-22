<template>
    <Layout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Expenses</h2>
                    <p class="text-sm text-gray-500">
                        Record what the lounge spends, with an optional receipt photo.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="openCategories"
                        class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <i class="pi pi-tags"></i> Manage Categories
                    </button>
                    <button
                        @click="openTemplates"
                        class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <i class="pi pi-replay"></i> Manage Templates
                    </button>
                    <button
                        @click="openAdd()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <i class="pi pi-plus"></i> Add Expense
                    </button>
                </div>
            </div>

            <!-- Summary cards: FIXED periods (Manila), not the filters below -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">This month</p>
                    <p class="text-2xl font-bold text-gray-800 tabular-nums mt-1">
                        {{ summary ? money(summary.this_month) : "…" }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ summary ? summary.labels.this_month + " so far" : "" }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Last month</p>
                    <p class="text-2xl font-bold text-gray-800 tabular-nums mt-1">
                        {{ summary ? money(summary.last_month) : "…" }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ summary ? summary.labels.last_month + ", whole month" : "" }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Change</p>
                    <p
                        class="text-2xl font-bold tabular-nums mt-1"
                        :class="changeClass"
                    >
                        <template v-if="!summary">…</template>
                        <template v-else-if="summary.change_percent === null">—</template>
                        <template v-else>
                            <i
                                v-if="summary.change_percent !== 0"
                                class="pi text-lg"
                                :class="summary.change_percent > 0 ? 'pi-arrow-up' : 'pi-arrow-down'"
                            ></i>
                            {{ Math.abs(summary.change_percent) }}%
                        </template>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        <template v-if="summary && summary.change_percent === null">
                            Nothing spent {{ summary.labels.compare }} last month
                        </template>
                        <template v-else-if="summary">
                            {{ summary.labels.today }} vs {{ summary.labels.compare }}
                            ({{ money(summary.last_month_same_days) }})
                        </template>
                    </p>
                </div>
            </div>

            <!-- Due this month: active recurring templates (hidden when there are none) -->
            <div
                v-if="dueItems.length > 0"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
            >
                <div class="flex items-center gap-2 mb-3">
                    <i class="pi pi-calendar text-indigo-500"></i>
                    <h3 class="font-semibold text-gray-800">Due this month</h3>
                    <span class="text-xs text-gray-400">{{ dueMonth }}</span>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="d in dueItems"
                        :key="d.id"
                        class="flex flex-wrap items-center gap-x-4 gap-y-2 py-2.5"
                        :class="d.status === 'overdue' ? 'bg-red-50 -mx-5 px-5' : ''"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full shrink-0"
                            :style="{ backgroundColor: colorOf(d.category?.color) }"
                        ></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ d.description || d.category?.name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ d.category?.name }}
                                <span v-if="d.category_archived" class="text-amber-600"
                                    >· category archived</span
                                >
                                <template v-if="d.default_amount">
                                    · usually {{ money(d.default_amount) }}</template
                                >
                            </p>
                        </div>

                        <!-- Logged -->
                        <template v-if="d.status === 'logged'">
                            <span class="text-sm text-green-700 font-medium">
                                <i class="pi pi-check text-xs mr-1"></i>{{ money(d.logged_amount) }}
                                <span class="text-xs text-gray-400 font-normal">
                                    on {{ formatDate(d.logged_at) }}
                                    <template v-if="d.logged_count > 1">({{ d.logged_count }} entries)</template>
                                </span>
                            </span>
                        </template>

                        <!-- Not logged: due / overdue -->
                        <template v-else>
                            <span
                                v-if="d.status === 'overdue'"
                                class="text-xs font-semibold text-red-700 bg-red-100 rounded-full px-2 py-0.5"
                                >Overdue</span
                            >
                            <span
                                class="text-sm"
                                :class="d.status === 'overdue' ? 'text-red-700 font-medium' : 'text-gray-600'"
                                >Due on {{ d.due_label }}</span
                            >
                            <button
                                @click="logTemplate(d)"
                                class="text-xs font-medium px-3 py-1.5 rounded-lg"
                                :class="
                                    d.status === 'overdue'
                                        ? 'bg-red-600 hover:bg-red-700 text-white'
                                        : 'bg-indigo-600 hover:bg-indigo-700 text-white'
                                "
                            >
                                Log it
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Totals for the current filters + donut -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <p class="text-xs text-gray-400 uppercase tracking-wide">
                    Total for the current filters
                </p>
                <p class="text-4xl font-bold text-gray-800 tabular-nums mt-1">
                    {{ money(totalAmount) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ pagination.total }}
                    {{ pagination.total === 1 ? "expense" : "expenses" }}
                </p>

                <div class="mt-5 flex flex-col lg:flex-row gap-6 lg:items-center">
                    <!-- Per-category totals -->
                    <div class="flex-1 min-w-0 space-y-2">
                        <div
                            v-for="ct in categoryTotals"
                            :key="ct.id"
                            class="flex items-center gap-3 text-sm"
                        >
                            <span
                                class="w-2.5 h-2.5 rounded-full shrink-0"
                                :style="{ backgroundColor: colorOf(ct.color) }"
                            ></span>
                            <span class="w-40 truncate text-gray-700">
                                {{ ct.name }}
                                <span v-if="!ct.is_active" class="text-xs text-gray-400"
                                    >(archived)</span
                                >
                            </span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full"
                                    :style="{
                                        width: sharePercent(ct.total) + '%',
                                        backgroundColor: colorOf(ct.color),
                                    }"
                                ></div>
                            </div>
                            <span class="w-28 text-right font-medium text-gray-800 tabular-nums">
                                {{ money(ct.total) }}
                            </span>
                            <span class="w-10 text-right text-xs text-gray-400">{{
                                ct.count
                            }}</span>
                        </div>
                    </div>

                    <!-- Donut: follows the filters (same database totals as the list) -->
                    <div class="w-full lg:w-64 shrink-0 flex items-center justify-center">
                        <div
                            v-if="categoryTotals.length === 0"
                            class="w-48 h-48 rounded-full border-[14px] border-gray-100 flex items-center justify-center text-sm text-gray-400 text-center"
                        >
                            No expenses yet
                        </div>
                        <Chart
                            v-else
                            type="doughnut"
                            :data="donutData"
                            :options="donutOptions"
                            class="w-56 h-56"
                        />
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap items-center gap-3"
            >
                <DateRangePicker initial-mode="month" @change="onRangeChange" />
                <select
                    v-model="filters.category_id"
                    @change="reload"
                    class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                >
                    <option value="">All categories</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.name }}{{ c.is_active ? "" : " (archived)" }}
                    </option>
                </select>
                <div class="relative flex-1 min-w-[12rem]">
                    <i
                        class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
                    ></i>
                    <input
                        v-model="filters.search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Search description..."
                        class="w-full text-sm border border-gray-200 rounded-lg pl-9 pr-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="loading && expenses.length === 0" class="text-center py-12 text-gray-400">
                    <i class="pi pi-spin pi-spinner text-2xl"></i>
                </div>
                <p v-else-if="listError" class="text-center py-12 text-sm text-red-500">
                    {{ listError }}
                </p>
                <p v-else-if="expenses.length === 0" class="text-center py-12 text-sm text-gray-400">
                    No expenses for these filters.
                </p>
                <div v-else class="overflow-x-auto" :class="loading ? 'opacity-60' : ''">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                <th class="px-4 py-3 font-medium">Date &amp; time</th>
                                <th class="px-4 py-3 font-medium">Category</th>
                                <th class="px-4 py-3 font-medium">Description</th>
                                <th class="px-4 py-3 font-medium text-right">Amount</th>
                                <th class="px-4 py-3 font-medium text-center">Receipt</th>
                                <th class="px-4 py-3 font-medium">Recorded by</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="e in expenses" :key="e.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ formatDateTime(e.spent_at) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-2 text-gray-700">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full"
                                            :style="{ backgroundColor: colorOf(e.category?.color) }"
                                        ></span>
                                        {{ e.category?.name || "—" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 max-w-xs truncate" :title="e.description">
                                    {{ e.description || "—" }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 tabular-nums whitespace-nowrap">
                                    {{ money(e.amount) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        v-if="e.has_receipt"
                                        @click="viewReceipt(e)"
                                        title="View receipt"
                                        class="text-indigo-500 hover:text-indigo-700"
                                    >
                                        <i class="pi pi-image"></i>
                                    </button>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                    {{ e.creator?.name || "—" }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button
                                        @click="openEdit(e)"
                                        title="Edit"
                                        class="text-gray-400 hover:text-indigo-600 px-2 py-1"
                                    >
                                        <i class="pi pi-pencil"></i>
                                    </button>
                                    <button
                                        @click="confirmDelete = e"
                                        title="Delete"
                                        class="text-gray-400 hover:text-red-600 px-2 py-1"
                                    >
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pages -->
                <div
                    v-if="pagination.last_page > 1"
                    class="flex items-center justify-between px-4 py-3 border-t border-gray-100 text-sm"
                >
                    <button
                        @click="goToPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1 || loading"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40"
                    >
                        <i class="pi pi-chevron-left text-xs"></i> Previous
                    </button>
                    <span class="text-gray-500">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <button
                        @click="goToPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page || loading"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40"
                    >
                        Next <i class="pi pi-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Add / Edit Expense ── -->
        <div
            v-if="form"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    {{ form.id ? "Edit Expense" : "Add Expense" }}
                </h3>
                <div
                    v-if="form.template"
                    class="mb-4 text-xs rounded-lg px-3 py-2"
                    :class="form.templateCategoryArchived ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700'"
                >
                    <i class="pi pi-replay mr-1"></i>
                    From template: {{ form.template.description || form.template.category?.name }}
                    <span v-if="form.templateCategoryArchived" class="block mt-1">
                        Its category ({{ form.template.category?.name }}) is archived. Pick an active one.
                    </span>
                </div>

                <label class="block text-xs font-medium text-gray-500 mb-1">Category *</label>
                <select
                    v-model="form.expense_category_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                >
                    <option value="" disabled>Pick a category</option>
                    <option v-for="c in formCategories" :key="c.id" :value="c.id">
                        {{ c.name }}{{ c.is_active ? "" : " (archived)" }}
                    </option>
                </select>

                <label class="block text-xs font-medium text-gray-500 mb-1">Amount (₱) *</label>
                <input
                    v-model="form.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    placeholder="0.00"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />

                <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                <input
                    v-model="form.description"
                    type="text"
                    maxlength="255"
                    placeholder="e.g. November electric bill"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />

                <label class="block text-xs font-medium text-gray-500 mb-1">Date &amp; time *</label>
                <input
                    v-model="form.spent_at"
                    type="datetime-local"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-1 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                <p class="text-xs text-gray-400 mb-3">Philippine time. Can be in the past.</p>

                <label class="block text-xs font-medium text-gray-500 mb-1">Receipt photo</label>
                <!-- New photo picked -->
                <div v-if="form.receiptPreview" class="mb-3">
                    <img :src="form.receiptPreview" alt="New receipt" class="max-h-40 rounded-lg border border-gray-200" />
                    <button @click="clearNewReceipt" class="text-xs text-gray-500 hover:text-red-600 mt-1">
                        <i class="pi pi-times text-xs"></i> Don't use this photo
                    </button>
                </div>
                <!-- Existing photo (editing) -->
                <div v-else-if="form.hasReceipt && !form.removeReceipt" class="mb-3 flex items-center gap-3 text-sm">
                    <button @click="viewReceipt(form.source)" class="text-indigo-600 hover:underline">
                        <i class="pi pi-image mr-1"></i>View current receipt
                    </button>
                    <button @click="form.removeReceipt = true" class="text-gray-500 hover:text-red-600">
                        Remove
                    </button>
                </div>
                <p v-else-if="form.removeReceipt" class="mb-2 text-xs text-red-500">
                    The current receipt will be removed when you save.
                    <button @click="form.removeReceipt = false" class="underline ml-1">Undo</button>
                </p>
                <input
                    ref="receiptInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    @change="onReceiptPicked"
                    class="w-full text-sm text-gray-500 mb-1 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-sm"
                />
                <p class="text-xs text-gray-400 mb-3">
                    JPG, PNG or WebP, up to 5 MB.
                    <template v-if="form.hasReceipt">Picking a new photo replaces the current one.</template>
                </p>

                <p v-if="form.error" class="text-sm text-red-500 mb-3">{{ form.error }}</p>

                <button
                    @click="saveExpense"
                    :disabled="form.busy"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                >
                    {{ form.busy ? "Saving..." : form.id ? "Save Changes" : "Add Expense" }}
                </button>
                <button
                    @click="closeForm"
                    :disabled="form.busy"
                    class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </div>

        <!-- ── Delete confirmation ── -->
        <div
            v-if="confirmDelete"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Delete this expense?</h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ money(confirmDelete.amount) }} ·
                    {{ confirmDelete.category?.name || "—" }} ·
                    {{ formatDateTime(confirmDelete.spent_at) }}
                    <span v-if="confirmDelete.description" class="block mt-1">
                        "{{ confirmDelete.description }}"
                    </span>
                </p>
                <p v-if="deleteError" class="text-sm text-red-500 mb-3">{{ deleteError }}</p>
                <div class="flex gap-2">
                    <button
                        @click="confirmDelete = null; deleteError = null"
                        :disabled="deleting"
                        class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="doDelete"
                        :disabled="deleting"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium disabled:opacity-50"
                    >
                        {{ deleting ? "Deleting..." : "Delete" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Receipt viewer ── -->
        <div
            v-if="receipt"
            class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[60] p-4"
            @click.self="closeReceipt"
        >
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-gray-700 truncate">
                        Receipt · {{ money(receipt.expense.amount) }} ·
                        {{ receipt.expense.category?.name || "" }}
                    </p>
                    <button @click="closeReceipt" class="text-gray-400 hover:text-gray-600">
                        <i class="pi pi-times"></i>
                    </button>
                </div>
                <div class="flex items-center justify-center min-h-[12rem] bg-gray-50 rounded-lg">
                    <i v-if="receipt.loading" class="pi pi-spin pi-spinner text-2xl text-gray-400"></i>
                    <p v-else-if="receipt.error" class="text-sm text-red-500">{{ receipt.error }}</p>
                    <img v-else :src="receipt.url" alt="Receipt" class="max-h-[75vh] max-w-full rounded-lg" />
                </div>
            </div>
        </div>

        <!-- ── Manage Templates ── -->
        <div
            v-if="showTemplates"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-gray-800">Recurring Templates</h3>
                    <button @click="closeTemplates" class="text-gray-400 hover:text-gray-600">
                        <i class="pi pi-times"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mb-4">
                    Presets for monthly bills. They never add expenses by themselves;
                    "Log it" fills in the Add Expense form.
                </p>

                <!-- Add / edit one template -->
                <div class="border border-gray-200 rounded-lg p-3 mb-4">
                    <p class="text-xs font-medium text-gray-500 mb-2">
                        {{ tplForm.id ? "Edit template" : "New template" }}
                    </p>
                    <select
                        v-model="tplForm.expense_category_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    >
                        <option value="" disabled>Category</option>
                        <option v-for="c in templateCategories" :key="c.id" :value="c.id">
                            {{ c.name }}{{ c.is_active ? "" : " (archived)" }}
                        </option>
                    </select>
                    <input
                        v-model="tplForm.description"
                        type="text"
                        maxlength="255"
                        placeholder="Description, e.g. Monthly rent"
                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                    <div class="flex gap-2">
                        <input
                            v-model="tplForm.default_amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            placeholder="Usual amount (optional)"
                            class="flex-1 min-w-0 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        />
                        <input
                            v-model.number="tplForm.due_day"
                            type="number"
                            min="1"
                            max="31"
                            placeholder="Due day"
                            title="Day of the month it's due (1–31)"
                            class="w-24 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        />
                    </div>
                    <p class="text-xs text-gray-400 mt-1 mb-2">
                        Leave the amount empty for bills that change (like electricity).
                        Due day 29–31 becomes the last day in shorter months.
                    </p>
                    <div class="flex gap-2">
                        <button
                            @click="saveTemplate"
                            :disabled="tplForm.busy"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium disabled:opacity-50"
                        >
                            {{ tplForm.id ? "Save" : "Add template" }}
                        </button>
                        <button
                            v-if="tplForm.id"
                            @click="resetTplForm"
                            class="text-sm text-gray-400 hover:text-gray-600 px-1"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <p v-if="tplMessage" class="text-sm mb-3" :class="tplMessageOk ? 'text-green-600' : 'text-red-500'">
                    {{ tplMessage }}
                </p>

                <p v-if="templates.length === 0" class="text-sm text-gray-400 text-center py-4">
                    No templates yet.
                </p>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="tpl in templates"
                        :key="tpl.id"
                        class="flex items-center gap-3 py-2"
                        :class="tpl.is_active ? '' : 'opacity-60'"
                    >
                        <span class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: colorOf(tpl.category?.color) }"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ tpl.description || tpl.category?.name }}
                                <span v-if="!tpl.is_active" class="text-xs text-gray-400 font-normal">(off)</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ tpl.category?.name }}<span v-if="tpl.category && !tpl.category.is_active"> (archived)</span>
                                · due day {{ tpl.due_day }}
                                · {{ tpl.default_amount ? money(tpl.default_amount) : "amount varies" }}
                            </p>
                        </div>
                        <button @click="editTemplate(tpl)" title="Edit" class="text-gray-400 hover:text-indigo-600 px-1">
                            <i class="pi pi-pencil text-sm"></i>
                        </button>
                        <button
                            @click="toggleTemplate(tpl)"
                            :disabled="tplBusyId === tpl.id"
                            class="text-xs border rounded-lg px-2 py-1 disabled:opacity-50"
                            :class="tpl.is_active ? 'border-gray-200 text-gray-600 hover:bg-gray-50' : 'border-indigo-200 text-indigo-600 hover:bg-indigo-50'"
                        >
                            {{ tpl.is_active ? "Turn off" : "Turn on" }}
                        </button>
                        <button
                            @click="deleteTemplate(tpl)"
                            :disabled="tplBusyId === tpl.id"
                            title="Delete (its past expenses are kept)"
                            class="px-1 disabled:opacity-30"
                            :class="tplDeleteConfirm === tpl.id ? 'text-red-600' : 'text-gray-400 hover:text-red-600'"
                        >
                            <i class="pi pi-trash text-sm"></i>
                            <span v-if="tplDeleteConfirm === tpl.id" class="text-xs ml-1">Sure?</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Manage Categories ── -->
        <div
            v-if="showCategories"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Expense Categories</h3>
                    <button @click="closeCategories" class="text-gray-400 hover:text-gray-600">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <!-- Add / edit one category -->
                <div class="border border-gray-200 rounded-lg p-3 mb-4">
                    <p class="text-xs font-medium text-gray-500 mb-2">
                        {{ catForm.id ? "Edit category" : "New category" }}
                    </p>
                    <div class="flex gap-2">
                        <input
                            v-model="catForm.color"
                            type="color"
                            title="Color"
                            class="w-10 h-9 border border-gray-300 rounded-lg p-0.5 cursor-pointer"
                        />
                        <input
                            v-model="catForm.name"
                            type="text"
                            maxlength="100"
                            placeholder="Category name"
                            @keydown.enter="saveCategory"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        />
                        <button
                            @click="saveCategory"
                            :disabled="catForm.busy || !catForm.name.trim()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium disabled:opacity-50"
                        >
                            {{ catForm.id ? "Save" : "Add" }}
                        </button>
                        <button
                            v-if="catForm.id"
                            @click="resetCatForm"
                            class="text-sm text-gray-400 hover:text-gray-600 px-1"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <p v-if="catMessage" class="text-sm mb-3" :class="catMessageOk ? 'text-green-600' : 'text-red-500'">
                    {{ catMessage }}
                </p>

                <div class="divide-y divide-gray-100">
                    <div
                        v-for="c in categories"
                        :key="c.id"
                        class="flex items-center gap-3 py-2"
                        :class="c.is_active ? '' : 'opacity-60'"
                    >
                        <span class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: colorOf(c.color) }"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                {{ c.name }}
                                <span v-if="!c.is_active" class="text-xs text-gray-400 font-normal">(archived)</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ c.expenses_count }} {{ c.expenses_count === 1 ? "expense" : "expenses" }}
                            </p>
                        </div>
                        <button
                            @click="editCategory(c)"
                            title="Edit name and color"
                            class="text-gray-400 hover:text-indigo-600 px-1"
                        >
                            <i class="pi pi-pencil text-sm"></i>
                        </button>
                        <button
                            @click="toggleCategory(c)"
                            :disabled="catBusyId === c.id"
                            class="text-xs border rounded-lg px-2 py-1 disabled:opacity-50"
                            :class="
                                catArchiveConfirm === c.id
                                    ? 'border-amber-300 bg-amber-50 text-amber-700'
                                    : c.is_active
                                      ? 'border-gray-200 text-gray-600 hover:bg-gray-50'
                                      : 'border-indigo-200 text-indigo-600 hover:bg-indigo-50'
                            "
                        >
                            <template v-if="catArchiveConfirm === c.id">
                                {{ c.templates_count }}
                                {{ c.templates_count === 1 ? "template uses" : "templates use" }}
                                this category · Archive anyway?
                            </template>
                            <template v-else>{{ c.is_active ? "Archive" : "Unarchive" }}</template>
                        </button>
                        <button
                            @click="deleteCategory(c)"
                            :disabled="!c.can_delete || catBusyId === c.id"
                            :title="c.can_delete ? 'Delete' : 'Has expenses — archive it instead'"
                            class="px-1 disabled:opacity-30 disabled:cursor-not-allowed"
                            :class="catDeleteConfirm === c.id ? 'text-red-600' : 'text-gray-400 hover:text-red-600'"
                        >
                            <i class="pi pi-trash text-sm"></i>
                            <span v-if="catDeleteConfirm === c.id" class="text-xs ml-1">Sure?</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

const API = "/api/admin/expenses";
const MAX_RECEIPT_BYTES = 5 * 1024 * 1024;
const RECEIPT_TYPES = ["image/jpeg", "image/png", "image/webp"];
const DEFAULT_COLOR = "#6366f1";

export default {
    name: "AdminExpenses",
    components: { DateRangePicker },

    data() {
        return {
            // List
            filters: { from: null, to: null, category_id: "", search: "" },
            expenses: [],
            pagination: { current_page: 1, last_page: 1, total: 0 },
            totalAmount: "0.00",
            categoryTotals: [],
            loading: false,
            listError: null,
            page: 1,

            // Categories
            categories: [],
            showCategories: false,
            catForm: { id: null, name: "", color: DEFAULT_COLOR, busy: false },
            catMessage: null,
            catMessageOk: true,
            catBusyId: null,
            catDeleteConfirm: null, // two clicks to delete
            catArchiveConfirm: null, // two clicks to archive when templates use the category

            // Recurring templates
            dueItems: [],
            dueMonth: "",
            templates: [],
            showTemplates: false,
            tplForm: { id: null, expense_category_id: "", description: "", default_amount: "", due_day: "", busy: false },
            tplMessage: null,
            tplMessageOk: true,
            tplBusyId: null,
            tplDeleteConfirm: null, // two clicks to delete

            // Add / edit expense
            form: null,

            // Delete expense
            confirmDelete: null,
            deleting: false,
            deleteError: null,

            // Receipt viewer
            receipt: null, // { expense, url, loading, error }

            // Summary cards (fixed periods, not the filters)
            summary: null,
        };
    },

    computed: {
        // "Add expense" lists active categories only; editing also keeps the
        // expense's own category even if it was archived since.
        formCategories() {
            const current = this.form?.originalCategoryId;
            return this.categories.filter((c) => c.is_active || c.id === current);
        },
        // New templates: active categories only; editing keeps the template's own (even archived)
        templateCategories() {
            const current = this.tplForm.originalCategoryId;
            return this.categories.filter((c) => c.is_active || c.id === current);
        },
        // Up = spending went up = red; down = green
        changeClass() {
            const p = this.summary?.change_percent;
            if (p === null || p === undefined || p === 0) return "text-gray-800";
            return p > 0 ? "text-red-600" : "text-green-600";
        },
        // Donut: one slice per category, in its color (totals come from the database)
        donutData() {
            return {
                labels: this.categoryTotals.map((c) => c.name),
                datasets: [
                    {
                        data: this.categoryTotals.map((c) => Number(c.total) || 0),
                        backgroundColor: this.categoryTotals.map((c) => this.colorOf(c.color)),
                        borderWidth: 2,
                        borderColor: "#ffffff",
                        hoverOffset: 6,
                    },
                ],
            };
        },
        donutOptions() {
            const total = this.categoryTotals.reduce((s, c) => s + (Number(c.total) || 0), 0);
            return {
                cutout: "62%",
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // the list beside it is the legend
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = Number(ctx.raw) || 0;
                                const pct = total > 0 ? ((v / total) * 100).toFixed(1) : "0.0";
                                return ` ${this.money(v)} (${pct}%)`;
                            },
                        },
                    },
                },
            };
        },
        largestCategoryTotal() {
            return Math.max(0, ...this.categoryTotals.map((c) => Number(c.total) || 0));
        },
    },

    async mounted() {
        this.fetchSummary();
        this.fetchDue();
        await this.fetchCategories();
        // The DateRangePicker fires its first change ("This month") on its own,
        // which loads the list.
    },

    beforeUnmount() {
        clearTimeout(this._searchTimer);
        this.revokePreview();
        this.closeReceipt();
    },

    methods: {
        headers() {
            return { Authorization: `Bearer ${localStorage.getItem("auth-token")}` };
        },

        errorText(e, fallback) {
            const errors = e.response?.data?.errors;
            if (errors) return Object.values(errors)[0][0];
            return e.response?.data?.message || fallback;
        },

        // ── List ──
        onRangeChange({ from, to }) {
            this.filters.from = from;
            this.filters.to = to;
            this.reload();
        },

        onSearchInput() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => this.reload(), 400);
        },

        reload() {
            this.page = 1;
            this.fetchExpenses();
        },

        goToPage(p) {
            this.page = p;
            this.fetchExpenses();
        },

        async fetchExpenses() {
            const requestNo = (this._requestNo = (this._requestNo || 0) + 1);
            this.loading = true;
            this.listError = null;
            try {
                const res = await axios.get(API, {
                    headers: this.headers(),
                    params: {
                        from: this.filters.from || null,
                        to: this.filters.to || null,
                        category_id: this.filters.category_id || null,
                        search: this.filters.search.trim() || null,
                        page: this.page,
                    },
                });
                if (requestNo !== this._requestNo) return; // a newer filter won
                this.expenses = res.data.expenses;
                this.pagination = res.data.pagination;
                this.totalAmount = res.data.total_amount;
                this.categoryTotals = res.data.category_totals;
            } catch (e) {
                if (requestNo !== this._requestNo) return;
                this.listError = this.errorText(e, "Couldn't load expenses.");
            } finally {
                if (requestNo === this._requestNo) this.loading = false;
            }
        },

        // Summary cards: their own call, so changing a filter doesn't reload them
        async fetchSummary() {
            try {
                const res = await axios.get(`${API}/summary`, { headers: this.headers() });
                this.summary = res.data;
            } catch (e) {
                console.error("Failed to load the expense summary", e);
            }
        },

        // ── Recurring templates ──
        async fetchDue() {
            try {
                const res = await axios.get(`${API}/due-this-month`, { headers: this.headers() });
                this.dueItems = res.data.items;
                this.dueMonth = res.data.month;
            } catch (e) {
                console.error("Failed to load templates due this month", e);
            }
        },

        // "Log it": the Add Expense form, pre-filled; saving links it to the template
        logTemplate(item) {
            this.openAdd(item);
        },

        async fetchTemplates() {
            try {
                const res = await axios.get(`${API}/templates`, { headers: this.headers() });
                this.templates = res.data.templates;
            } catch (e) {
                console.error("Failed to load templates", e);
            }
        },

        async openTemplates() {
            this.resetTplForm();
            this.tplMessage = null;
            this.showTemplates = true;
            await Promise.all([this.fetchTemplates(), this.fetchCategories()]);
        },

        closeTemplates() {
            this.showTemplates = false;
            this.tplDeleteConfirm = null;
        },

        resetTplForm() {
            this.tplForm = { id: null, originalCategoryId: null, expense_category_id: "", description: "", default_amount: "", due_day: "", busy: false };
        },

        editTemplate(tpl) {
            this.tplMessage = null;
            this.tplForm = {
                id: tpl.id,
                originalCategoryId: tpl.expense_category_id,
                expense_category_id: tpl.expense_category_id,
                description: tpl.description || "",
                default_amount: tpl.default_amount ? Number(tpl.default_amount) : "",
                due_day: tpl.due_day,
                busy: false,
            };
        },

        showTplMessage(ok, text) {
            this.tplMessageOk = ok;
            this.tplMessage = text;
        },

        async afterTemplateChange() {
            await Promise.all([this.fetchTemplates(), this.fetchDue(), this.fetchCategories()]);
        },

        async saveTemplate() {
            const f = this.tplForm;
            if (f.busy) return;
            if (!f.expense_category_id) return this.showTplMessage(false, "Pick a category.");
            const day = Number(f.due_day);
            if (!Number.isInteger(day) || day < 1 || day > 31) return this.showTplMessage(false, "The due day must be 1 to 31.");
            if (f.default_amount !== "" && f.default_amount !== null && !(Number(f.default_amount) > 0)) {
                return this.showTplMessage(false, "The usual amount must be more than ₱0, or empty.");
            }
            f.busy = true;
            const body = {
                expense_category_id: f.expense_category_id,
                description: f.description.trim() || null,
                default_amount: f.default_amount === "" || f.default_amount === null ? null : f.default_amount,
                due_day: day,
            };
            try {
                if (f.id) {
                    await axios.put(`${API}/templates/${f.id}`, body, { headers: this.headers() });
                    this.showTplMessage(true, "Template saved.");
                } else {
                    await axios.post(`${API}/templates`, body, { headers: this.headers() });
                    this.showTplMessage(true, "Template added.");
                }
                this.resetTplForm();
                await this.afterTemplateChange();
            } catch (e) {
                this.showTplMessage(false, this.errorText(e, "Couldn't save the template."));
                f.busy = false;
            }
        },

        async toggleTemplate(tpl) {
            this.tplBusyId = tpl.id;
            try {
                const res = await axios.post(`${API}/templates/${tpl.id}/toggle`, {}, { headers: this.headers() });
                this.showTplMessage(true, res.data.message);
                await this.afterTemplateChange();
            } catch (e) {
                this.showTplMessage(false, this.errorText(e, "Couldn't update the template."));
            } finally {
                this.tplBusyId = null;
            }
        },

        async deleteTemplate(tpl) {
            if (this.tplDeleteConfirm !== tpl.id) {
                this.tplDeleteConfirm = tpl.id; // first click: "Sure?"
                clearTimeout(this._tplDeleteTimer);
                this._tplDeleteTimer = setTimeout(() => (this.tplDeleteConfirm = null), 4000);
                return;
            }
            this.tplDeleteConfirm = null;
            this.tplBusyId = tpl.id;
            try {
                const res = await axios.delete(`${API}/templates/${tpl.id}`, { headers: this.headers() });
                this.showTplMessage(true, res.data.message);
                if (this.tplForm.id === tpl.id) this.resetTplForm();
                await this.afterTemplateChange();
            } catch (e) {
                this.showTplMessage(false, this.errorText(e, "Couldn't delete the template."));
            } finally {
                this.tplBusyId = null;
            }
        },

        formatDate(iso) {
            if (!iso) return "—";
            return new Date(iso).toLocaleDateString("en-PH", { timeZone: "Asia/Manila", month: "short", day: "numeric" });
        },

        // ── Formatting ──
        money(value) {
            const n = Number(value) || 0;
            return "₱" + n.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDateTime(iso) {
            if (!iso) return "—";
            return new Date(iso).toLocaleString("en-PH", {
                timeZone: "Asia/Manila",
                month: "short",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit",
            });
        },

        colorOf(color) {
            return color || "#9ca3af";
        },

        sharePercent(total) {
            if (!this.largestCategoryTotal) return 0;
            return Math.max(2, Math.round(((Number(total) || 0) / this.largestCategoryTotal) * 100));
        },

        // "YYYY-MM-DDTHH:mm" in Manila time, for <input type="datetime-local">
        toManilaInput(date) {
            const parts = Object.fromEntries(
                new Intl.DateTimeFormat("en-CA", {
                    timeZone: "Asia/Manila",
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit",
                    hour: "2-digit",
                    minute: "2-digit",
                    hourCycle: "h23",
                })
                    .formatToParts(date)
                    .map((p) => [p.type, p.value]),
            );
            return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
        },

        // ── Add / edit ──
        // template = a "Due this month" item: pre-fill from it and link the expense to it
        openAdd(template = null) {
            // Only a real "Due this month" item counts as a template (never a click event)
            if (!template || !template.category) template = null;
            this.revokePreview();
            const archived = !!template?.category_archived;
            this.form = {
                id: null,
                source: null,
                originalCategoryId: null,
                template,
                templateCategoryArchived: archived,
                expense_category_id: template && !archived ? template.category.id : "",
                amount: template?.default_amount ? Number(template.default_amount) : "",
                description: template?.description || "",
                spent_at: this.toManilaInput(new Date()), // now, Philippine time
                receiptFile: null,
                receiptPreview: null,
                hasReceipt: false,
                removeReceipt: false,
                busy: false,
                error: null,
            };
        },

        openEdit(e) {
            this.revokePreview();
            this.form = {
                id: e.id,
                source: e,
                originalCategoryId: e.expense_category_id,
                template: null,
                templateCategoryArchived: false,
                expense_category_id: e.expense_category_id,
                amount: Number(e.amount),
                description: e.description || "",
                spent_at: this.toManilaInput(new Date(e.spent_at)),
                receiptFile: null,
                receiptPreview: null,
                hasReceipt: !!e.has_receipt,
                removeReceipt: false,
                busy: false,
                error: null,
            };
        },

        closeForm() {
            this.revokePreview();
            this.form = null;
        },

        onReceiptPicked(event) {
            const file = event.target.files?.[0];
            if (!file || !this.form) return;
            if (!RECEIPT_TYPES.includes(file.type)) {
                this.form.error = "The receipt must be a JPG, PNG or WebP photo.";
                event.target.value = "";
                return;
            }
            if (file.size > MAX_RECEIPT_BYTES) {
                this.form.error = "The receipt photo must be 5 MB or smaller.";
                event.target.value = "";
                return;
            }
            this.form.error = null;
            this.revokePreview();
            this.form.receiptFile = file;
            this.form.receiptPreview = URL.createObjectURL(file);
            this.form.removeReceipt = false;
        },

        clearNewReceipt() {
            this.revokePreview();
            if (this.form) {
                this.form.receiptFile = null;
                this.form.receiptPreview = null;
            }
            if (this.$refs.receiptInput) this.$refs.receiptInput.value = "";
        },

        revokePreview() {
            if (this.form?.receiptPreview) URL.revokeObjectURL(this.form.receiptPreview);
        },

        async saveExpense() {
            const f = this.form;
            if (!f || f.busy) return;
            if (!f.expense_category_id) return (f.error = "Pick a category.");
            if (!(Number(f.amount) > 0)) return (f.error = "The amount must be more than ₱0.");
            if (!f.spent_at) return (f.error = "Set the date and time.");

            f.busy = true;
            f.error = null;
            const body = new FormData();
            body.append("expense_category_id", f.expense_category_id);
            body.append("amount", f.amount);
            body.append("description", f.description || "");
            body.append("spent_at", f.spent_at); // Philippine time; the server saves UTC
            if (!f.id && f.template) body.append("expense_template_id", f.template.id); // "Log it"
            if (f.receiptFile) body.append("receipt", f.receiptFile);
            if (f.id && f.removeReceipt && !f.receiptFile) body.append("remove_receipt", "1");

            try {
                await axios.post(f.id ? `${API}/${f.id}` : API, body, { headers: this.headers() });
                this.closeForm();
                await Promise.all([this.fetchExpenses(), this.fetchCategories(), this.fetchSummary(), this.fetchDue()]);
            } catch (e) {
                f.error = this.errorText(e, "Couldn't save the expense.");
                f.busy = false;
            }
        },

        // ── Delete expense ──
        async doDelete() {
            if (!this.confirmDelete || this.deleting) return;
            this.deleting = true;
            this.deleteError = null;
            try {
                await axios.delete(`${API}/${this.confirmDelete.id}`, { headers: this.headers() });
                this.confirmDelete = null;
                // Last item on a later page: step back a page
                if (this.expenses.length === 1 && this.page > 1) this.page--;
                await Promise.all([this.fetchExpenses(), this.fetchCategories(), this.fetchSummary(), this.fetchDue()]);
            } catch (e) {
                this.deleteError = this.errorText(e, "Couldn't delete the expense.");
            } finally {
                this.deleting = false;
            }
        },

        // ── Receipt (private file, loaded with the admin token) ──
        async viewReceipt(expense) {
            this.closeReceipt();
            this.receipt = { expense, url: null, loading: true, error: null };
            try {
                const res = await axios.get(`${API}/${expense.id}/receipt`, {
                    headers: this.headers(),
                    responseType: "blob",
                });
                if (this.receipt?.expense !== expense) return; // closed meanwhile
                this.receipt.url = URL.createObjectURL(res.data);
            } catch (e) {
                if (this.receipt) this.receipt.error = "Couldn't load the receipt.";
            } finally {
                if (this.receipt) this.receipt.loading = false;
            }
        },

        closeReceipt() {
            if (this.receipt?.url) URL.revokeObjectURL(this.receipt.url);
            this.receipt = null;
        },

        // ── Categories ──
        async fetchCategories() {
            try {
                const res = await axios.get(`${API}/categories`, { headers: this.headers() });
                this.categories = res.data.categories;
            } catch (e) {
                console.error("Failed to load expense categories", e);
            }
        },

        async openCategories() {
            this.resetCatForm();
            this.catMessage = null;
            this.showCategories = true;
            await this.fetchCategories();
        },

        closeCategories() {
            this.showCategories = false;
            this.catDeleteConfirm = null;
        },

        resetCatForm() {
            this.catForm = { id: null, name: "", color: DEFAULT_COLOR, busy: false };
        },

        editCategory(c) {
            this.catMessage = null;
            this.catForm = { id: c.id, name: c.name, color: c.color || DEFAULT_COLOR, busy: false };
        },

        showCatMessage(ok, text) {
            this.catMessageOk = ok;
            this.catMessage = text;
        },

        async afterCategoryChange() {
            await this.fetchCategories();
            await Promise.all([this.fetchExpenses(), this.fetchDue()]); // names, colors, "archived" notes
        },

        async saveCategory() {
            const f = this.catForm;
            if (f.busy || !f.name.trim()) return;
            f.busy = true;
            try {
                const body = { name: f.name.trim(), color: f.color };
                if (f.id) {
                    await axios.put(`${API}/categories/${f.id}`, body, { headers: this.headers() });
                    this.showCatMessage(true, `${body.name} saved.`);
                } else {
                    await axios.post(`${API}/categories`, body, { headers: this.headers() });
                    this.showCatMessage(true, `${body.name} added.`);
                }
                this.resetCatForm();
                await this.afterCategoryChange();
            } catch (e) {
                this.showCatMessage(false, this.errorText(e, "Couldn't save the category."));
                f.busy = false;
            }
        },

        async toggleCategory(c) {
            // Archiving a category that templates use: one extra click with a warning
            if (c.is_active && c.templates_count > 0 && this.catArchiveConfirm !== c.id) {
                this.catArchiveConfirm = c.id;
                clearTimeout(this._catArchiveTimer);
                this._catArchiveTimer = setTimeout(() => (this.catArchiveConfirm = null), 5000);
                return;
            }
            this.catArchiveConfirm = null;
            this.catBusyId = c.id;
            try {
                const res = await axios.post(`${API}/categories/${c.id}/toggle`, {}, { headers: this.headers() });
                this.showCatMessage(true, res.data.message);
                await this.afterCategoryChange();
            } catch (e) {
                this.showCatMessage(false, this.errorText(e, "Couldn't update the category."));
            } finally {
                this.catBusyId = null;
            }
        },

        async deleteCategory(c) {
            if (!c.can_delete) return;
            if (this.catDeleteConfirm !== c.id) {
                this.catDeleteConfirm = c.id; // first click: "Sure?"
                clearTimeout(this._catDeleteTimer);
                this._catDeleteTimer = setTimeout(() => (this.catDeleteConfirm = null), 4000);
                return;
            }
            this.catDeleteConfirm = null;
            this.catBusyId = c.id;
            try {
                const res = await axios.delete(`${API}/categories/${c.id}`, { headers: this.headers() });
                this.showCatMessage(true, res.data.message);
                if (this.catForm.id === c.id) this.resetCatForm();
                await this.afterCategoryChange();
            } catch (e) {
                this.showCatMessage(false, this.errorText(e, "Couldn't delete the category."));
            } finally {
                this.catBusyId = null;
            }
        },
    },
};
</script>
