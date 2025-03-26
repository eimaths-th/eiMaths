<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TeachingPeriod;
use Illuminate\Http\Request;
use Carbon\Carbon;


class TeachingPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // withotu id 
        $teaching_periods = TeachingPeriod::all();
        $departments = Department::whereNotIn('id', [1, 4, 5])->get();
        // dd($teaching_periods);
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Thursday', 'Friday'];
        $time = [
            1 => '09:00-10:00',
            2 => '10:00-11:00',
            3 => '11:00-12:00',
            4 => '13:00-14:00',
            5 => '14:00-15:00',
            6 => '15:00-16:00',
            7 => '16:00-17:00',
            8 => '17:00-18:00',
        ];

        // $loopupdate = TeachingPeriod::where('department_id', 3)->get();

        // foreach ($loopupdate as $loopupdating) {
        //     // Get the current time period
        //     $period = $loopupdating->period;

        //     // Split the time period into start and end times
        //     list($start, $end) = explode('-', $period);

        //     // Parse the start time and add 30 minutes
        //     $start_time = Carbon::createFromFormat('H:i', $start)->addMinutes(30);
        //     $end_time = Carbon::createFromFormat('H:i', $end)->addMinutes(30);

        //     // Format the new start and end times
        //     $new_period = $start_time->format('H:i') . '-' . $end_time->format('H:i');

        //     // Update the teaching period record
        //     $loopupdating->period = $new_period;
        //     $loopupdating->save(); // Save the updated period
        // }
        // foreach ($departments as $department) {
        //     foreach ($days as $day) {
        //         // Check if the day is Saturday or Sunday
        //         if ($day == 'Saturday' || $day == 'Sunday') {
        //             // Loop through the time periods
        //             for ($i = 1; $i <= 7; $i++) {
        //                 // Create teaching period
        //                 $teaching_period = new TeachingPeriod();
        //                 $teaching_period->department_id = $department->id;
        //                 $teaching_period->day = $day;
        //                 $teaching_period->period = $time[$i];
        //                 $teaching_period->save();
        //             }
        //         } else {
        //             // Loop through the time periods
        //             for ($i = 2; $i <= 8; $i++) {
        //                 // Create teaching period
        //                 $teaching_period = new TeachingPeriod();
        //                 $teaching_period->department_id = $department->id;
        //                 $teaching_period->day = $day;
        //                 $teaching_period->period = $time[$i];
        //                 $teaching_period->save();
        //             }
        //         }
        //     }
        // }
        return view('teaching_period.index', [
            'teaching_periods' => $teaching_periods,
            'departments' => $departments,
            'days' => $days,

        ],);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function find(Request $request, $department_id, $day)
    {
        // Retrieve teaching periods for the selected department and day
        $teachingPeriods = TeachingPeriod::where('department_id', $department_id)
            ->where('day', $day)
            ->get();

        $html = '';
        foreach ($teachingPeriods as $teachingPeriod) {
            $html .= '
                <tr>
                    <td>' . $teachingPeriod->department->name . '</td>
                    <td>' . $teachingPeriod->day . '</td>
                    <td>' . $teachingPeriod->period . '</td>
                    <td>
                        <form action="' . route('TeachingPeriod.destroy', $teachingPeriod->id) . '" method="POST">
                        
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <input type="hidden" name="_method" value="DELETE">
            
                        </form>
                    </td>
                </tr>';
        }


        //  <a class="btn btn-info" href="' . route('TeachingPeriod.edit', $teachingPeriod->id) . '">Edit</a>
        // Return the data as a JSON response
        return response()->json(['html' => $html]);
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
