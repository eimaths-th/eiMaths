<?php

namespace App\Domain\Elearning;

use App\Models\Contact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class StudentSessionService
{
    public const SESSION_KEY = 'elearning_session_token';
    public const LOCAL_SESSION_KEYS = [self::SESSION_KEY, 'answersOfQuestions', 'quizStartTime', 'startTimeInSeconds'];

    public function Start(Contact $student, Request $request): void
    {
        $session_token = bin2hex(random_bytes(32));
        $updated_contacts = Contact::query()->whereKey($student->getKey())->update([
            'elearning_session_token_hash' => hash('sha256', $session_token),
        ]);

        if ($updated_contacts !== 1) {
            throw (new ModelNotFoundException)->setModel(Contact::class, [$student->getKey()]);
        }

        $request->session()->put(self::SESSION_KEY, $session_token);
    }

    public function IsCurrent(Contact $student, Request $request): bool
    {
        $session_token = $request->session()->get(self::SESSION_KEY);
        if (!is_string($session_token) || $session_token === '') {
            return false;
        }

        $stored_hash = Contact::query()->whereKey($student->getKey())->value('elearning_session_token_hash');

        return is_string($stored_hash) && $stored_hash !== ''
            && hash_equals($stored_hash, hash('sha256', $session_token));
    }

    public function End(Contact $student, Request $request): void
    {
        $session_token = $request->session()->get(self::SESSION_KEY);
        if (is_string($session_token) && $session_token !== '') {
            Contact::query()->whereKey($student->getKey())
                ->where('elearning_session_token_hash', hash('sha256', $session_token))
                ->update(['elearning_session_token_hash' => null]);
        }

        $request->session()->forget(self::LOCAL_SESSION_KEYS);
    }
}
