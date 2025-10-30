<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactQuiz;
use App\Models\ImageOption;
use App\Models\Option;
use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class eLeanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $level = ['K1', 'K2', 'P1', 'P2', 'P3', 'P4', 'P5', 'P6',];
    public $term = ['1', '2', '3', '4'];

    public function getQuizId()
    {
        if (Auth::guard('student')->check()) {
            $student = Contact::find(Auth::guard('student')->user()->id);
            $id = $student ? $student->id : null;
        } elseif (Auth::check()) {
            $id = Auth::user()->id;
        } else {
            // Not authenticated at all
            abort(403, 'Unauthorized');
        }
        $getQuizAssign = ContactQuiz::where([
            'contact_id' => $id
        ])->get();
        if ($getQuizAssign && $getQuizAssign->count() > 0) {
            # code...
            $quizIds = $getQuizAssign->pluck('quiz_id');
        } else {
            # code...
            $quizId = Quiz::all();
            $quizIds = $quizId->pluck('id');
        }
        return $quizIds;
    }

    public function home()
    {
        $quizIds = $this->getQuizId();

        $levels = $this->level;
        // dd($getQuizAssign,$quizIds,$levels);

        $public_quizzes_level = Quiz::whereHas('questions')
            ->where('public', true)
            ->whereIn('id', $quizIds)
            ->orderBy('level', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('level');

        $public_quizzes = Quiz::whereHas('questions')
            ->where('public', true)
            ->whereIn('id', $quizIds)
            ->orderBy('id', 'desc')
            ->withCount('questions')
            ->get();

        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where('published', true)
            ->whereIn('id', $quizIds)
            ->orderBy('level', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('level');



        $levelCheck = 0;
        $header = 'eiMaths Quiz';
        // dd($public_quizzes_level);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'levels', 'public_quizzes_level', 'levelCheck'));
    }

    public function homeLevel($level)
    {
      
        $quizIds = $this->getQuizId();
        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $public_quizzes_term = Quiz::whereHas('questions')
            ->where(['public' => true, 'level' => $level])
            ->whereIn('id', $quizIds)
            ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('term');
        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where(['published' => true, 'level' => $level])
            ->whereIn('id', $quizIds)
            ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('term');
        $levelCheck = 1;
        $header = 'eiMaths Quiz Grade ' . $level;
        // dd($public_quizzes_term);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'public_quizzes_term', 'levelCheck', 'level'));
    }
    public function homeTerm($level, $term)
    {
        
        $quizIds = $this->getQuizId();

        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $public_quizzes_term = Quiz::whereHas('questions')
            ->where(['public' => true, 'level' => $level, 'term' => $term])
            ->whereIn('id', $quizIds)
            // ->orderBy('term', 'asc')
           // ->orderBy('term', 'DESC')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('section')->sortKeys();
        // ->groupBy('term');
        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where(['published' => true, 'level' => $level, 'term' => $term])
            ->whereIn('id', $quizIds)
            // ->orderBy('term', 'asc')
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get()
            ->groupBy('section')->sortKeys();

        // ->groupBy('term');
        $levelCheck = 2;
        $header = 'eiMaths Quiz Grade ' . $level . ' Term ' . $term;
        //  dd($public_quizzes_term);

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'public_quizzes_term', 'levelCheck', 'level', 'term'));
    }

    public function homeSection($level, $term, $section)
    {
        $quizIds = $this->getQuizId();

        $public_quizzes = Quiz::whereHas('questions')->where('public', true)->orderBy('id', 'desc')->withCount('questions')->get();
        $public_quizzes_term = Quiz::whereHas('questions')
            ->where(['public' => true, 'level' => $level, 'term' => $term, 'section' => $section])
            ->whereIn('id', $quizIds)
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get();
        $registered_only_quizzes = Quiz::whereHas('questions')
            ->where(['published' => true, 'level' => $level, 'term' => $term, 'section' => $section])
            ->whereIn('id', $quizIds)
            ->orderBy('id', 'Desc')
            ->withCount('questions')
            ->get();
        $levelCheck = 3;
        $header = 'eiMaths Quiz Grade ' . $level . ' Term ' . $term . ' Level-' . $section;

        return view('home.home', compact('public_quizzes', 'registered_only_quizzes', 'header', 'public_quizzes_term', 'levelCheck', 'level', 'term', 'section'));
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
        if (Auth::guard('student')->check()) {
            $user_id = Auth::guard('student')->id();
            $type_user = 'student';
        } elseif (Auth::check()) {
            $user_id = Auth::id();
            $type_user = 'user';
        } else {
        }
        $tests = Test::select('id', 'result', 'time_spent', 'user_id', 'quiz_id', 'created_at')
            ->where('user_id', $user_id)
            ->where('type_user', $type_user)
            ->with(['quiz' => function ($query) {
                $query->select('id', 'title','level','term','section');
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
                $query->select('id', 'title','level','term','section');
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

    public function checkAnswer(Request $request)
    {
        $request->validate([
            'userAnswer' => 'required',
            'type' => 'required|string',
            'correctAnswer' => 'required',
        ]);

        $userAnswer = $request->userAnswer;
        $type = $request->type;
        $correctAnswer = $request->correctAnswer;
        $answerUser = '';
        $answerCorrect = '';
        $result = '';
        $checkCorrect = '';
        $imageUrl = '';

        if ($type === 'options') {
            $answerUser = Option::find($userAnswer)->text ?? '';
            $answerCorrect = Option::find($correctAnswer)->text ?? '';
        } elseif ($type === 'image') {
            $answerUser = ImageOption::find($userAnswer)->img_name ?? '';
            $answerCorrect = ImageOption::find($correctAnswer)->img_name ?? '';
            $imageUrl = asset('images_options/' . $answerCorrect);
        } elseif ($type === 'written') {
            $answerUser = $userAnswer;
            $answerCorrect = $correctAnswer;
        }

        if ($answerUser === $answerCorrect) {
            $result = 'Correct! : ';
            $checkCorrect = 1;
        } else {
            $result = 'Incorrect. The correct answer is : ';
            $checkCorrect = 0;
        }

        return response()->json([
            'success' => true,
            'result' => $result,
            'answerCorrect' => $answerCorrect,
            'answerUser' => $answerUser,
            'type' => $type,
            'checkCorrect' => $checkCorrect,
            'imageUrl' => $imageUrl,
        ]);
    }


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
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
