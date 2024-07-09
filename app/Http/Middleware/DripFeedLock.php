<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\TextLesson;

class DripFeedLock
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
        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;
        } else {
            $userId = null;
        }

        $textLesson = TextLesson::find($request->lesson_id);

        if (
            !empty($user) && //user is logged in
            !empty($textLesson) && //the requested text lesson exists
            (!$user->isUser() || userHasDripFeedAccess($request->lesson_id, $userId))
        ) {
            return $next($request);
        } else {
            return back();
        }
    }
}
