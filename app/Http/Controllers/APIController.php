<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\level;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Models\term;
use App\Models\bookuse;
use App\Models\Histrories;
use App\Models\HistoryStudentImg;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use Carbon\Carbon;

class APIController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token, 'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function googleLogin(Request $request)
    {
        // Verify Google token

        $response = Http::get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
            'id_token' => $request->idToken,
        ]);

        //dd($response);
//&& $response['aud'] === env('GOOGLE_CLIENT_ID')
        if ($response->status() === 200) {
            // Google token is valid, find user by email
            $user = User::where('email', $response['email'])->first();

            if ($user) {
                // User exists, authenticate user
                Auth::login($user);

                // Create token using Laravel Passport
                $token = $user->createToken('Personal Access Token')->accessToken;

                return response()->json([
                    'token' => $token, 'user_id' => $user->id,
                    'user_name' => $user->name,
                ]);
            } else {
                // User doesn't exist, return error
                return response()->json(['error' => 'User ' . $response['email'] . ' not found. Please sign in with your Google account.'], 404);
            }
        } else {
            // Invalid Google token
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

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

        //$student = Contact::find($std_id);
        $order = Order::where('cid', $request->get('std'))->get();
        $order_detail = OrderDetailList::select('level as name', DB::raw('(SELECT id FROM levels WHERE levels.name = order_detail_lists.level) as id'))
            ->whereIn('order_id', $order->pluck('id'))
            ->groupBy('name')->get();

        $levels = $order_detail;

        /* $student = Contact::find($request->get('std'));
        if ($student) {
            $lv1 = $student->level;
            $lv2 = $student->level2;
            $levels = level::whereBetween('id', [$lv1, $lv2])->get();
        } else {
            $levels = [];
        } */


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


    public function get_product_id($std_id)
    {
        // Retrieve orders for the given student ID
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();

        // Extract order IDs from the orders
        $orderIds = $orders->pluck('id');

        // Retrieve order details and concatenate product IDs
        $orderDetails = OrderDetail::whereIn('order_id', $orderIds)
            ->selectRaw('order_id, GROUP_CONCAT(product_id) as product_ids')
            ->groupBy('order_id')
            ->get();

        // Initialize an empty array to store book uses
        $bookuses = [];

        // Loop through each order detail to extract product IDs and retrieve book uses
        foreach ($orderDetails as $orderDetail) {
            // Explode the concatenated product IDs string
            $productIdsArray = explode(',', $orderDetail->product_ids);

            // Ensure $productIdsArray contains at least 2 elements before accessing
            if (count($productIdsArray) >= 2) {
                // Retrieve book uses where the id falls between the range specified in $productIdsArray
                $bookuses[] = BookUse::whereBetween('id', [$productIdsArray[0], $productIdsArray[1]])->get()->toArray();
            }
        }

        // Flatten the array of book uses and convert each element to object
        $bookuses = collect(array_merge(...$bookuses))->map(function ($item) {
            return (object) $item;
        });

        //dd($bookuses);

        return $bookuses;
    }

    public function reorder_remain($std_id, $term, $level_id)
    {
        $histories = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->orderByRaw("CONCAT(date, ' ', stime) ASC")->get();


        /*  $matchingBookUses = [];
        foreach ($this->get_product_id($std_id) as $bookuse) {
            if ($bookuse->term_id == $term && $bookuse->level_id == $level_id) {
                $book_id = $bookuse->id;
                $matchingBookUses[$bookuse->id] = $bookuse->qty;
            }
        } */
        //$contact = Contact::where('id', $std_id)->first();
        // $orderlist = OrderDetailList::where('order_id', $contact->order)->get();
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderlist = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();



        $book_id = null;
        $matchingBookUses = [];
        foreach ($this->get_product_id($std_id) as $bookuse) {
            if ($bookuse->term_id == $term && $bookuse->level_id == $level_id) {

                $matchingBookUses[$bookuse->id] = $bookuse->qty;
                //break;
                foreach ($orderlist as $order) {
                    if ($order->book == $bookuse->name) {
                        $book_id = $bookuse->id;
                    }
                }
            }
        }

        //last record term
        $lasthistoriesCount = Histrories::where([
            ['student_id', $std_id],
            ['level_id', $level_id],
            ['term', '<', $term],
        ])->orderByRaw("CONCAT(date, ' ', stime) ASC")->count();

        //dd($lasthistoriesCount);

        if ($matchingBookUses[$book_id] < 10 && $lasthistoriesCount !== 0) {
            $count = 4;
        } else {
            $count = 9;
        }

        foreach ($histories as $history) {
            // Update course_remaining
            $history->update(['course_remaining' => $count]);

            // Decrement count for next record
            $count--;

            // Ensure count doesn't go below 0
            if ($count < 0) {
                $count = 0;
            }
        }
    }

    public function saveHistories(Request $request)
    {

        /* $durationHours = $request->get('hour');

            $updatedTime = Carbon::createFromFormat('H:i:s', $time)
                ->addHours($durationHours)
                ->format('H:i:s'); */
        $centre = $request->get('centre');
        $std_id = $request->get('student');
        $level_id = $request->get('subject');
        $term = $request->get('term');
        $bookuse = $request->get('book');
        $datetolearn = $request->get('date');
        $stime = $request->get('stime');
        $etime = $request->get('etime');
        $comment = $request->input('comments');
        $userid = $request->input('userid');
        $time =  $request->get('stime');


        $level = level::find($level_id);
        $level_name = $level->name;

        //contact data
        $contact = Contact::find($std_id);

        $history = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->get();

        //$contact = Contact::where('id', $std_id)->first();
        //$orderlist = OrderDetailList::where('order_id', $contact->order)->get();
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderlist = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();

        $book_id = null;
        $qty = null;
        $matchingBookUses = [];
        foreach ($this->get_product_id($std_id) as $bookused) {
            if ($bookused->term_id == $term && $bookused->level_id == $level_id) {

                $matchingBookUses[$bookused->id] = $bookused->qty;
                //break;
                foreach ($orderlist as $order) {
                    if ($order->book == $bookused->name) {
                        $book_id = $bookused->id;
                        $qty = $bookused->qty;
                    }
                }
            }
        }

        //term qty
        /*  foreach ($this->get_product_id($std_id) as $bookused) {
            if ($bookused->term_id == $term && $bookused->level_id == $level_id) {
                $qty = $bookused->qty;
                //break;
            }
        } */

        if ($qty === null) {
            return response()->json(['success' => false, 'message' => 'Student Not Register This Level Or This Term '], 400);
        }

        if ($history->isEmpty()) {
            $start_date = Carbon::parse($request->input('date'));
            $end_date = $start_date->copy()->addMonths(4);
            $course_remaining = 9;
        } elseif ($history->count() < $qty) {
            $lastHistory = $history->sortByDesc('date')->first();
            $firstHistory = $history->sortBy('date')->first();
            $course_remaining = $lastHistory->course_remaining - 1;
            //$course_remaining =  9 - $history->count();
            $start_date = $firstHistory->start_date;
            $end_date = $firstHistory->end_date;

            if ($contact->level2 == $level_id && $contact->term2 == $term && $course_remaining == 0) {
                //auto discontinue
                $contactd = [
                    'discontinued' => 1,
                    'discontinued_date' => date("Y-m-d"),
                    'discontinued_reason' => 'End of course',
                ];

                $contact->update($contactd);
            }
        } else {
            return response()->json(['message' => 'Study time is full  for this term'], 400);
        }

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();

            $newname = Str::random(40) . "_" . $imageName;
            $image->move(public_path('file_upload'), $newname);
        } else {
            $newname = '';
        }

        $signature = $newname;

        $create = Histrories::create([
            'centre' => $centre,
            'student_id' => $std_id,
            'teacher_id' => $userid,
            'level_id' => $level_id,
            'level_name' => $level_name,
            'term' => $term,
            'bookuse' => $bookuse,
            'date' => $datetolearn,
            'stime' => $stime,
            'etime' => $etime,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'comment' => $comment,
            'signature' => $signature,
            'course_remaining' => $course_remaining
        ]);

        $uploadedFileNames = [];

        if ($request->hasFile('files')) {
            $uploadedFileNames = [];
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $newname = Str::random(40) . "_" . $file->getClientOriginalName();
                    $file->move(public_path('file_upload'), $newname);
                    $uploadedFileNames[] = $newname;

                    // Save file details to database
                    $historyStudentImg = new HistoryStudentImg;
                    $historyStudentImg->history_id = $create->id; // Assuming $create is available
                    // Add other necessary fields
                    $historyStudentImg->student_id = $std_id;
                    $historyStudentImg->img = $newname;
                    $historyStudentImg->save();
                }
            }
        }

        $this->reorder_remain($std_id, $term, $level_id);



        /* $files = $request->file('files');
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $newname = Str::random(40) . "_" . $file->getClientOriginalName();
                    $file->move(public_path('file_upload'), $newname);
                    $uploadedFileNames[] = $newname;

                    $historyStudentImg = new HistoryStudentImg;
                    $historyStudentImg->history_id = $create->id;
                    $historyStudentImg->student_id = $std_id;
                    $historyStudentImg->img = $newname;
                    $historyStudentImg->save();
                }
            } */

        //}
        return response()->json([
            'message' => 'Data Save Successfully',
            //'file' => $newname
        ], 200);

        //return response()->json(['message' => 'No file found'], 400);
    }

    public function saveFile(Request $request)
    {
        if ($request->hasFile('files')) {
            $uploadedFileNames = [];
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $newname = Str::random(40) . "_" . $file->getClientOriginalName();
                    $file->move(public_path('file_upload'), $newname);
                    $uploadedFileNames[] = $newname;
                }
            }

            return response()->json([
                'message' => 'Files saved successfully',
                'uploaded_files' => $uploadedFileNames
            ]);
        }

        return response()->json(['message' => 'No file found'], 400);
    }
}
