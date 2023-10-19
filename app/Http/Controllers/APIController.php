<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\level;
use App\Models\Contact;
use App\Models\Department;
use App\Models\term;
use App\Models\bookuse;
use App\Models\Histrories;
use Carbon\Carbon;

class APIController extends Controller
{


    public function searchDepartment(Request $request)
    {
        //$query = $request->query('query');

        $results = Department::orderBy("code", "asc")
            ->where('status', 1)
            ->get();

        return response()->json($results);
    }
    public function searchStudent(Request $request)
    {
        //$query = $request->query('query');

        $results = Contact::orderBy("code", "asc")
            ->where('centre', $request->get('centre'))
            ->get();

        return response()->json($results);
    }
    //
    public function searchSubject(Request $request)
    {
        //$query = $request->query('query');

        $student = Contact::find($request->get('std'));
        $lv1 = $student->level;
        $lv2 = $student->level2;
        $levels = level::whereBetween('id', [$lv1, $lv2])->get();

        return response()->json($levels);
    }


    public function searchTerm(Request $request)
    {
        //$query = $request->query('query');

        $data = term::select(
            "terms.id as id",
            "terms.name as name",
        )

            ->where('level_id', $request->get('level'))
            ->where('status', 1)
            ->orderBy("terms.name", "asc")
            ->get();

        return response()->json($data);
    }

    public function searchBook(Request $request)
    {
        //$query = $request->query('query');

        $data = bookuse::select(
            "bookuses.id as id",
            "bookuses.name as name",
        )

            ->where('level_id', $request->get('level'))
            ->where('term_id', $request->get('term'))
            //->where('type', 1)
            ->where('status', 1)
            ->orderBy("bookuses.name", "asc")
            ->get();

        return response()->json($data);
    }



    public function saveHistories(Request $request)
    {

       /*  if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('file_upload'), $newname); */

            //endtime
            $time =  $request->get('stime'); // Time string
            $durationHours = $request->get('hour'); // Duration in hours to add

            $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                ->addHours($durationHours)
                ->format('H:i:s');

            $course = new Histrories([
                'centre' => $request->get('centre'),
                'student_id' => $request->get('student'),
                'level_id' => $request->get('subject'),
                'term' => $request->get('term'),
                'bookuse' => $request->get('book'),
                //'level_id' => $request->get('level'),
                'date' => $request->get('date'),
                'stime' => $request->get('stime'),
                'etime' => $updatedTime,
                //'description' => $request->get('des'),
                //'signature' => $newname,
            ]);
            $course->save();

            return response()->json([
                'message' => 'Data Save Successfully',
                //'file' => $newname
            ]);
       /*  }

        return response()->json(['message' => 'No file found'], 400); */
    }
}
