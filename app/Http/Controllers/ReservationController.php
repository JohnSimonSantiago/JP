<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Status;
use App\Models\equipment;
use App\Models\reservation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\equipment_status;
use Illuminate\Support\Facades\DB;
use App\Models\reservation_details;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ReservationController extends Controller
{
    public function submitReservation(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'customerName' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'roomNumber' => 'required|integer|between:1,25',
        'reservationType' => 'required|integer|in:1,2', // 1: Short Time, 2: Overnight
        'roomType' => 'required|integer|in:1,2', // 1: Ordinary, 2: Standard
    ]);

    // Create new reservation
    $newReservation = new Reservation();
    $newReservation->customerName = $request->customerName;
    $newReservation->address = $request->address;
    $newReservation->roomNumber = $request->roomNumber;
    $newReservation->reservationType = $request->reservationType; // 1 or 2
    $newReservation->roomType = $request->roomType; // 1 or 2
    $newReservation->statusID = 1; // Default status

    // Save the new reservation
    $res = $newReservation->save();

    // Return a response
    return response()->json(['success' => $res], $res ? 200 : 500);
}

    


    public function getReservations(Request $request) {
        $getReservation = reservation::all();
        return $getReservation;
    }
    
        
  
        
public function extendReservation(Request $request)
{
    // Validate the request
    $validatedData = $request->validate([
        'ID' => 'required|exists:reservations,reservationNumber', // Ensure the reservation exists
        'additionalHours' => 'required|integer|min:1|max:5', // Validation for the hours
    ]);

    // Find the reservation
    $reservation = Reservation::find($validatedData['ID']);
    
    // Update the additional hours
    $reservation->additionalHours += $validatedData['additionalHours'];
    $reservation->save();

    return response()->json(['message' => 'Additional hours added successfully!']);
}

public function reduceReservation(Request $request)
    {
        $request->validate([
            'ID' => 'required|exists:reservations,reservationNumber',
            'reducedHours' => 'required|integer|min:1|max:5',
            'adminPassword' => 'required|string',
        ]);

        // Get the admin user (user with ID 1)
        $admin = User::find(1);

        // Check if the provided password matches the admin's password
        if (!Hash::check($request->adminPassword, $admin->password)) {
            return response()->json(['message' => 'Invalid admin password'], 403);
        }

        // Find the reservation
        $reservation = Reservation::where('reservationNumber', $request->ID)->firstOrFail();

        // Check if there are additional hours to reduce
        if ($reservation->additionalHours === null || $reservation->additionalHours === 0) {
            return response()->json(['message' => 'No additional hours to reduce'], 400);
        }

        // Reduce the additional hours
        $reservation->additionalHours -= $request->reducedHours;

        // Make sure the additional hours don't become negative
        if ($reservation->additionalHours < 0) {
            return response()->json(['message' => 'Cannot reduce hours beyond the current additional hours'], 400);
        }

        // If additional hours become 0, set it to null
        if ($reservation->additionalHours === 0) {
            $reservation->additionalHours = null;
        }

        // Save the updated reservation
        $reservation->save();

        return response()->json(['message' => 'Reservation hours reduced successfully']);
    }



        public function payRoomNow(Request $request)
        {
            $selectedReservation = reservation::find($request->ID);
            $selectedReservation->statusID = 2;
            $selectedReservation->save();
        }


      
        
       
        
        
}
