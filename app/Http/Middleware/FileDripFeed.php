<?php

namespace App\Http\Middleware;

use Closure;

class FileDripFeed
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
        $fileId = $request->route('file_id');
        $authUser = auth()->user();
        if ($authUser && $authUser->isUser()) {
            $userId = $authUser->id;
            if (!userCanAttemptFile($fileId, $userId)) {
                $toastData = [
                    'title' => trans('file.cannot_attempt'),
                    'msg' => trans('file.not_available'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);

            } else {
                return $next($request);
            }
        } else {
            if ($authUser->isAdmin() || $authUser->role != 'user' || $authUser->role != 'education') {
                return $next($request);
            }
            return redirect()->route('web.login');
        }

    }
}
