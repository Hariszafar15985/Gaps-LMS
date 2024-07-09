<?php

namespace App\Http\Middleware;

use App\Helpers\WebinarHelper;
use Closure;

class WebinarAccess
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
        $user = auth()->user();
        
        if ($user->isAdmin() || $user->isTeacher()) { //Admin & teachers have preview access
            return $next($request);
        } else {
            $slug = $request->route('slug');
            $webinarHelper = new WebinarHelper();
            $course = $webinarHelper->getCourseDataBySlug($slug, $user); 
            if ($user->isOrganizationPersonnel()) { //Is the user an organization Personnel?
                //let's see if this course is visible to organizations
                $organizationId = $user->isOrganization() ? $user->id : $user->organ_id;
                $visibleCourses = $webinarHelper->getWebinarsVisibleToOrganization($organizationId);
                $visibleCourseIds = $visibleCourses->pluck('id')->toArray();
                
                //Is the current course page one of the visible course pages?
                if (in_array($course->id, $visibleCourseIds)) {
                    return $next($request);
                }
            } else if ($user->isUser()) { //Is this a student?
                if ($course->checkUserHasBought($user)) {
                    return $next($request);
                }
            }
        }

        return abort(403);
    }
}
