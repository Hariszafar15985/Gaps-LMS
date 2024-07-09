<?php

namespace App\Http\Middleware;

use Closure;

class QuizDripLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $quizId = $request->route('id');
        $authUser = auth()->user();
        if ($authUser->isUser()) {
            $userId = $authUser->id;
            if (!userCanAttemptQuiz($quizId, $userId)) {
                $toastData = [
                    'title' => trans('quiz.cannot_attempt'),
                    'msg' => trans('quiz.not_available'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            } else {
                return $next($request);
            }
        } else {
            if ($authUser->isAdmin()) {
                return $next($request);
            }

            return redirect()->route('web.login');
        }

    }
}
