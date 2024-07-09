<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Support\Facades\Auth;

class PanelAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (auth()->check() and !auth()->user()->isAdmin()) {

            $referralSettings = getReferralSettings();
            view()->share('referralSettings', $referralSettings);

            $user = auth()->user();
            if ($user->role_name === Role::$user) {
                $missingEnrolmentStep = $user->getMissingUserEnrolmentStep();
                if ($missingEnrolmentStep > 0) {
                    $routeParameters = $request->route()->parameters();
                    if(
                        !in_array($request->route()->getName(),
                        ['get.panel.user_setting', 'get.panel_user_setting_main', 'post.panel_user_save_setting']
                        ) || (isset($routeParameters['step']) && (int)$routeParameters['step'] > $missingEnrolmentStep)
                    ) {
                        $request->session()->flash('enrollmentMessage', trans('panel.please_complete_enrolment_information_first'));
                        return redirect()->route('get.panel.user_setting', ['step' => $missingEnrolmentStep]);
                    }
                }
            }

            return $next($request);
        }

        return redirect('/login');
    }
}
