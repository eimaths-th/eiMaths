<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Department;
use App\Models\Option;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $level = ['K1', 'K2', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6',];
    public $term = ['1', '2', '3', '4'];
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();

        return view('home', [
            'centre' => $centre,
        ]);
    }

    public function welcome()
    {
        return view('home.welcome');
    }

    public function blank()
    {
        return view('home.blank');
    }
    public function generate()
    {
        return view('users.generate');
    }

    public function generate_save(Request $request)
    {
        $validator =  Validator::make($request->all(), [

            'password' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        $input = $request->all();
        $password = Hash::make($input['password']);
        return response()->json(['success' => 'Password Generated Success', 'generated' => $password]);
    }




    public function index2()
    {
        $query = Quiz::whereHas('questions')
            ->withCount('questions')
            ->when(auth()->guest() || !auth()->user()->is_admin, function ($query) {
                return $query->where('published', 1);
            })
            ->get();

        $public_quizzes = $query->where('public', 1);
        $registered_only_quizzes = $query->where('public', 0);

        return view('home2', compact('public_quizzes', 'registered_only_quizzes'));
    }

    public function show(Quiz $quiz)
    {
        return view('front.quizzes.show', compact('quiz'));
    }



    public function home3()
    {
        // dd($this->level);
        $levels = $this->level;
        $public_quizzes_level = Quiz::whereHas('questions')->where('public', true)->orderBy('level', 'asc')->orderBy('id', 'Desc')->withCount('questions')->get()->groupBy('level');
        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        // $registered_only_quizzes = Quiz::whereHas('questions')->where('published', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $registered_only_quizzes = Quiz::whereHas('questions')->where('published', true)->orderBy('level', 'asc')->orderBy('id', 'Desc')->withCount('questions')->get()->groupBy('level');
        $levelCheck = 0;
        // $query = Quiz::whereHas('questions')
        //     ->withCount('questions')
        //     ->when(auth()->guest() || !auth()->user()->is_admin, function ($query) {
        //         return $query->where('published', 1);
        //     })
        //     ->get();

        // $public_quizzes = $query->where('public', 1);
        // $registered_only_quizzes = $query->where('public', 0);

        $header = 'eiMaths Quiz';
        // dd($public_quizzes_level);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'levels', 'public_quizzes_level', 'levelCheck'));
    }
    public function homeLevel($level)
    {

        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $public_quizzes_term = Quiz::whereHas('questions')
            ->where(['public' => true, 'level' => $level])
            ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('term');
        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where(['published' => true, 'level' => $level])
            ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('term');
        $levelCheck = 1;
        $header = 'eiMaths Quiz Level ' . $level;
        // dd($public_quizzes_term);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'public_quizzes_term', 'levelCheck', 'level'));
    }
    public function homeTerm($level, $term)
    {

        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $public_quizzes_term = Quiz::whereHas('questions')
            ->where(['public' => true, 'level' => $level, 'term' => $term])
            // ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get();
        // ->groupBy('term');
        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where(['published' => true, 'level' => $level, 'term' => $term])
            // ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get();
        // ->groupBy('term');
        $levelCheck = 2;
        $header = 'eiMaths Quiz Level ' . $level . ' Term ' . $term;
        // dd($public_quizzes_term);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'public_quizzes_term', 'levelCheck', 'level', 'term'));
    }


    public function leaderboard(Request $request, $quiz_slug = '0')
    {

        $header = 'Leaderboard';
        $quizzes = Quiz::all();
        $quizId = 0;

        if ($quiz_slug != '0') {
            $quiz = Quiz::where('slug', $quiz_slug)->first();
            if ($quiz) {
                $quizId = $quiz->id;
            }
        }

        $tests = Test::query()
            ->whereHas('user')
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }, 'quiz' => function ($query) {
                $query->select('id', 'title');
                $query->withCount('questions');
            }])
            ->when($quizId > 0, function ($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            })
            ->orderBy('result', 'desc')
            ->orderBy('time_spent')
            ->get();

        return view('home.leaderboard', compact('header', 'quizzes', 'tests', 'quiz_slug'));
    }

    public function submitLeaderboard(Request $request)
    {
        $route = $request->route;
        // dd($request,$route);
        $request->validate([
            'quiz_slug' => 'required',
        ]);

        $quizSlug = $request->input('quiz_slug');

        if ($quizSlug == '0') {
            return redirect()->route($route);
        }

        return redirect()->route($route, ['quiz_slug' => $quizSlug]);
    }
    public function myResult()
    {
        $tests = Test::select('id', 'result', 'time_spent', 'user_id', 'quiz_id', 'created_at')
            ->where('user_id', auth()->id())
            ->with(['quiz' => function ($query) {
                $query->select('id', 'title', 'description');
                $query->withCount('questions');
            }])->orderBy('id', 'desc')
            ->paginate();
        $header = "My Results";

        return view('home.result-list', [
            'tests' => $tests,
            'header' => $header,

        ]);
    }
    public function tests(Request $request, $quiz_slug = '0')
    {
        $quizzes = Quiz::all();
        $header = 'Tests';
        $quizId = 0;

        if ($quiz_slug != '0') {
            $quiz = Quiz::where('slug', $quiz_slug)->first();
            if ($quiz) {
                $quizId = $quiz->id;
            }
        }

        $tests = Test::query()
            ->whereHas('user')
            ->with(['user' => function ($query) {
                $query->select('id', 'name');
            }, 'quiz' => function ($query) {
                $query->select('id', 'title');
            }])
            ->when($quizId > 0, function ($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            })
            ->withCount('questions')
            // ->orderBy('result', 'desc')
            // ->orderBy('time_spent')
            ->latest()
            ->get();
        return view('home.tests', [
            'tests' => $tests,
            'quizzes' => $quizzes,
            'quiz_slug' => $quiz_slug,
            'header' => $header,

        ]);
    }

    function normalizeNumber($number)
    {
        $digits = str_split($number);
        sort($digits);
        return implode('', $digits);
    }

    function normalizeNumber1()
    {
        $uniqueNumbers = array();

        for ($i = 0; $i < 1000; $i++) {
            $number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $normalized = $this->normalizeNumber($number); // Use method call with $this
            $uniqueNumbers[$normalized] = true;
        }

        // Get the array keys to show results
        $uniqueNumbers = array_keys($uniqueNumbers);
        sort($uniqueNumbers); // Sort numbers

        // Show results
        foreach ($uniqueNumbers as $key => $uniqueNumber) {
            // Print each unique number followed by a new line
            echo '[' . $key . '] = ' . $uniqueNumber . PHP_EOL ;
        }
        echo '<br> <br><br><br>';
        foreach ($uniqueNumbers as $key => $uniqueNumber1) {
            // Print each unique number followed by a new line
            echo $uniqueNumber1 . PHP_EOL ;
        }
        // print_r($uniqueNumbers);
    }
}
