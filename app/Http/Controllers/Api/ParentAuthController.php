<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Feedback;
use App\Models\HistoryStudentImg;
use App\Models\Histrories;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\HttpResponseException;
use Illuminate\Support\Facades\Log;
use \Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmation;

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

    public function passwordReset(Request $request)
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'required|exists:parents,id',
                'old_password' => 'required',
                'password' => 'required|confirmed|different:old_password|min:8|max:16',
            ], [
                'parent_id.required' => 'Parent ID is required',
                'parent_id.exists' => 'Parent not found',
                'old_password.required' => 'Old password is required',
                'password.required' => 'New password is required',
                'password.confirmed' => 'Password confirmation does not match',
                'password.different' => 'New password must be different from old password',
                'password.min' => 'New password must be at least 8 characters',
                'password.max' => 'New password must not exceed 16 characters',
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
    public function feedback(Request $request)
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'required|exists:parents,id',
                'title' => 'required',
                'description' => 'required',
                'image_feedback' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:51200', // 5MB
            ], [
                'parent_id.required' => 'Parent ID is required',
                'parent_id.exists' => 'Parent not found',
                'title.required' => 'Pls. Enter Title',
                'description.required' => 'Pls. Enter Description',
                'image_feedback.image' => 'The file must be an image',
                'image_feedback.mimes' => 'The image must be a file of type: jpeg,png,jpg,gif,svg',
                'image_feedback.max' => 'The image may not be greater than 5MB',
            ]);

            $user = Parents::find($validated['parent_id']);

            // Debug information (will be removed later)
            Log::info('Feedback Info:', [
                'parent_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description']
            ]);

            if ($request->hasFile('image_feedback')) {
                $image = $request->file('image_feedback');
                $randomString = Str::random(10); // Use Laravel's Str helper
                $imageName = time() . '_' . $randomString . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('feedback_images'), $imageName);
                $validated['image_feedback'] = $imageName;
            } else {
                $validated['image_feedback'] = null; // Set to null if no image is uploaded
            }

            $feedback = Feedback::create([
                'parent_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'image_feedback' => $validated['image_feedback'],

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback Send successfully',
                'msg' => 'Send successfully',
                'title' => 'Feedback'
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
    /**
     * Check student information by parent's email and phone number
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkstd(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'email' => 'required|email|max:100',
                'telp' => 'required|string|regex:/^[0-9]{10}$/',
                'type' => 'required|in:father,mother',
            ], [
                'email.required' => 'Email is required',
                'email.email' => 'Please enter a valid email address',
                'email.max' => 'Email may not be greater than 100 characters',
                'telp.required' => 'Phone number is required',
                'telp.regex' => 'Phone number must be 10 digits',
                'type.required' => 'Relationship type is required',
                'type.in' => 'Invalid relationship type. Must be either father or mother',
            ]);

            // Determine relationship type and build query
            if ($request->type === 'father') {
                # code...
                $std_list = Contact::where('father_email', $request->email)->where('father_mobile', $request->telp)->get();

                if ($std_list->count() <= 0) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Registered parent information not found. !!',
                        'message2' => 'Please contact eiMaths call center. telp : 061 620 8666 '
                    ]);
                }

                $parentname = $std_list->first()->father_name;
                $Relationship = $request->type;
            } elseif ($request->type === 'mother') {
                $std_list = Contact::where('mother_email', $request->email)->where('mother_mobile', $request->telp)->get();

                if ($std_list->count() <= 0) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Registered parent information not found. !!',
                        'message2' => 'Please contact eiMaths call center. telp : 061 620 8666'
                    ]);
                }
                $parentname = $std_list->first()->mother_name;
                $Relationship = $request->type;
            }


            $html_std_list = '';
            $number = 1;
            $html_std_list .= '
            <div class="row ">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="col-form-label form-label">Your Name : </label>
                            <label class="col-form-label form-label text-warning">' . $parentname . '</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="col-form-label form-label">Relationship : </label>
                            <label class="col-form-label form-label text-warning">' . $Relationship . '</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="col-form-label form-label">Your Email : </label>
                            <label class="col-form-label form-label text-warning">' . $request->email . '</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="col-form-label form-label">Your telp : </label>
                            <label class="col-form-label form-label text-warning">' . $request->telp . '</label>
                        </div>
                    </div>
                </div>
                ';
            foreach ($std_list as $key => $std) {
                $html_std_list .= '
            <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label form-label"> ' . $number++ . '.  </label>
                            <label class="col-form-label form-label"> Code : </label>
                            <label class="col-form-label form-label">' . $std->code . '</label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label form-label">Name : </label>
                            <label class="col-form-label form-label">' . $std->name . '</label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="col-form-label form-label">Nicname : </label>
                            <label class="col-form-label form-label">' . $std->nickname . '</label>
                        </div>
                    </div>
                </div>
                ';
            }

            $input = $request->all();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'successfully',
                'std_list' => $html_std_list,
                'dataStudent' => $std_list,
                'input' => $input
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('ParentAuthController@checkstd - Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function generateRandomPassword($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^^&*()_+';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $password;
    }
    public function checkUser(Request $request)
    {
        try {
            // Validate request
            $students = $request->students;
            $parent = $request->parent;

            $checkParentEmail = Parents::where('email', $parent['email'])->exists();
            $checkParentTelp = Parents::where('telp', $parent['telp'])->exists();

            if ($checkParentEmail) {
                return response()->json([
                    'error' => true,
                    'message' => 'Your Email has been registered. <br> Please contact call center: 093-258-5855'
                ]);
            }

            if ($checkParentTelp) {
                return response()->json([
                    'error' => true,
                    'message' => 'Your Telp has been registered. <br> Please contact call center: 093-258-5855'
                ]);
            }

            $std_ids_text = '';
            $std_ids = [];

            foreach ($students as $student) {
                if (isset($student['id'])) {
                    $std_ids_text .= $student['id'] . ',';
                    $std_ids[] = $student['id'];
                }
            }

            $std_ids_text = rtrim($std_ids_text, ',');

            $centre = $students[0]['centre'];
            $gender = '';

            if ($parent['type'] === 'mother') {

                $gender = 'female';
                $parent_name = $students[0]['mother_name'];
            } else {

                $gender = 'male';
                $parent_name = $students[0]['father_name'];
            }

            $nameParts = explode(' ', $parent_name);

            if (count($nameParts) > 1) {

                list($parent_first_name, $parent_last_name) = $nameParts;
            } else {

                list($parent_first_name) = $nameParts;
                $parent_last_name = '';
            }
            $randomPassword = $this->generateRandomPassword(6);
            $password_text = 'eimaths' . $randomPassword;
            $password = bcrypt($password_text);
            $parent_data = Parents::create([
                'student_id' => $std_ids_text,
                'centre_id' => $centre,
                'fname' => $parent_first_name,
                'lname' => $parent_last_name,
                'email' => $parent['email'],
                'password' => $password,
                'telp' => $parent['telp'],
                'address' => '-',
                'relationship' => $parent['type'],
                'gender' => $gender,
                'emergency_contact' => $parent['telp'],
                'notes' => '-',
            ]);
            return response()->json(['success' => true, 'message' => 'successfully', 'parent_data' => $parent_data, 'password_text' => $password_text]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('ParentAuthController@checkstd - Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function senMailParent(Request $request)
    {
        //


        try {

            $parentData = $request->input('parentData');
            $passwordText = $request->input('passwordText');
            $parent_data = Parents::find($parentData['id']);

            $parent_email = $parentData['email'];

            if ($parent_data && $parent_email) {

                Mail::to($parent_email)->send(new RegistrationConfirmation($parent_data, $passwordText));

                return response()->json([
                    'success' => true,
                    'message' => 'successfully',
                    'icon' => 'success',
                    'title' => 'Successfully sent to Email.',
                ]);
            } else {

                return response()->json([
                    'error' => true,
                    'message' => 'Please contact the call center.',
                    'icon' => 'error',
                    'title' => 'Found some errors !',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('ParentAuthController@checkstd - Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
