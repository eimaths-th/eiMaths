<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\HistoryStudentImg;
use App\Models\Histrories;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ParentAuthController extends Controller
{
    /**
     * Login parent and return token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $parent = Parents::where('email', $request->email)->first();

        if (!$parent || !Hash::check($request->password, $parent->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        // Create a new token for the parent
        $token = $parent->createToken('parent-token')->plainTextToken;

        $std_ids = $parent->students()->pluck('id');
        $std_ids_array = $std_ids->toArray();
        $histrories = Histrories::whereIn('student_id', $std_ids_array)->orderBy('id', 'desc')->get();
        $img_histories = HistoryStudentImg::whereIn('student_id', $std_ids_array)
            ->whereIn('history_id', $histrories->pluck('id'))
            ->orderBy('id', 'desc')->get();
        $students = Contact::whereIn('id', $std_ids_array)->orderBy('id', 'desc')->get();

        // dd($students);
        //add department name to students array
        foreach ($students as $student) {
            $student['centre_name'] = $student->department()->first()->name;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'name' => $parent->fname . ' ' . $parent->lname,
                    'email' => $parent->email,
                ],
                'histrories' => $histrories,
                'img_histories' => $img_histories,
                'students' => $students,
                'token' => $token,
            ],
        ]);
    }

    public function getStudent(Request $request)
    {
        //use parent_id to find parent
        $parent = Parents::find($request->parent_id);
        $std_ids = $parent->students()->pluck('id');
        $std_ids_array = $std_ids->toArray();
        $students = Contact::whereIn('id', $std_ids_array)->orderBy('id', 'desc')->get();

        $histrories = Histrories::where('student_id', $request->student_id)->orderBy('id', 'desc')->get();
        $img_histories = HistoryStudentImg::where('student_id', $request->student_id)
        ->whereIn('history_id', $histrories->pluck('id'))
        ->orderBy('id', 'desc')->get();
        foreach ($students as $student) {
            $student['centre_name'] = $student->department()->first()->name;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'name' => $parent->fname . ' ' . $parent->lname,
                    'email' => $parent->email,
                ],
                'histrories' => $histrories,
                'img_histories' => $img_histories,
                'students' => $students,
            ],
        ]);
    }

    /**
     * Logout parent (revoke token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        //delete all session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    // public function passwordReset(Request $request)
    // {

    //     try {
    //         $this->validate($request, [
    //             'old_password' => 'required|current_password',
    //             'password' => 'required',
    //             'password_confirmation' => 'required|same:password',
    //         ],
    //         [
    //             'old_password.required' => 'Old password is required',
    //             'old_password.current_password' => 'Old password is incorrect',
    //             'password.required' => 'New password is required',
    //             'password_confirmation.required' => 'Confirm password is required',
    //             'password_confirmation.same' => 'Confirm password must match new password',
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         throw new \Illuminate\Validation\ValidationException(
    //             response()->json(['errors' => $e->errors()], 422)
    //         );
    //     }
    //     $oldPassword = $request->input('old_password');
    //     $newPassword = $request->input('password');

    //     $user = Parents::find($request->input('parent_id'));

    //     if (!$user) {
    //         return response()->json(['error' => true, 'message' => 'User not found']);
    //     }

    //     if (!Hash::check($oldPassword, $user->password)) {
    //         return response()->json(['error' => true, 'message' => 'Old password is incorrect']);
    //     }

    //     $user->password = Hash::make($newPassword);
    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Password updated successfully',
    //         'msg' => 'Updated successfully',
    //         'title' => 'Password'
    //     ]);
    // }


    public function passwordReset(Request $request)
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'required|exists:parents,id',
                'old_password' => 'required',
                'password' => 'required|confirmed|different:old_password',
            ], [
                'parent_id.required' => 'Parent ID is required',
                'parent_id.exists' => 'Parent not found',
                'old_password.required' => 'Old password is required',
                'password.required' => 'New password is required',
                'password.confirmed' => 'Password confirmation does not match',
                'password.different' => 'New password must be different from old password',
            ]);

            $user = Parents::find($validated['parent_id']);

            // Debug information (will be removed later)
            Log::info('Debug Info:', [
                'provided_password' => $validated['old_password'],
                'stored_hash' => $user->password,
                'check_result' => Hash::check($validated['old_password'], $user->password)
            ]);

            if (!Hash::check($validated['old_password'], $user->password)) {
                Log::error('Password mismatch for user ID: ' . $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Old password is incorrect'
                ], 422);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
                'msg' => 'Updated successfully',
                'title' => 'Password'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the password'
            ], 500);
        }
    }
}
