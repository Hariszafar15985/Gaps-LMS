@php
    $progressSteps = [
        1 => [
            'name' => 'basic_information',
            'icon' => 'basic-info'
        ],

        2 => [
            'name' => 'images',
            'icon' => 'images'
        ],
    ];
    
    if($user->isUser()) {
        
        $studentSteps= [
            3 => [
                'name' => 'participant_details',
                'icon' => 'about'
            ],
            
            4 => [
                'name' => 'cultural_background',
                'icon' => 'about'
            ],
    
            5 => [
                'name' => 'special_needs',
                'icon' => 'about'
            ],
    
            6 => [
                'name' => 'employment',
                'icon' => 'about'
            ],
    
            7 => [
                'name' => 'educations',
                'icon' => 'graduate'
            ],
    
            8 => [
                'name' => 'study_reasons',
                'icon' => 'graduate'
            ],
    
            9 => [
                'name' => 'usi',
                'icon' => 'graduate'
            ],
        ];
        if ($user->isUser() && (!isset($studentDeclarationCompleted) || !$studentDeclarationCompleted)) {
            $studentSteps += [
                10 => [
                    'name' => 'student_declaration',
                    'icon' => 'graduate'
                ]
            ];
        }
        
        $studentSteps += [
            /* 11 => [
                'name' => 'experiences',
                'icon' => 'experiences'
            ],
    
            12 => [
                'name' => 'occupations',
                'icon' => 'skills'
            ], */

            /* 13 => [
                'name' => 'identity_and_financial',
                'icon' => 'financial'
            ] */
        ];
        // $progressSteps = array_merge($progressSteps, $studentSteps);
        $progressSteps = $progressSteps + $studentSteps;
    }
    

    //Only first two steps should be shown to Non-student Roles
    /* if(!$user->isUser()) {

        $progressSteps[8] =[
            'name' => 'zoom_api',
            'icon' => 'zoom'
        ];
    } */

    $currentStep = empty($currentStep) ? 1 : $currentStep;
@endphp


<div class="webinar-progress d-block d-lg-flex align-items-center p-15 panel-shadow bg-white rounded-sm">
    @php $progressItemsCount = 1; @endphp
    @foreach($progressSteps as $key => $step)
        <div class="progress-item d-flex align-items-center">
            <a href="@if(!empty($organization_id)) /panel/manage/{{ $user_type ?? 'instructors' }}/{{ $user->id }}/edit/step/{{ $key }} @else /panel/setting/step/{{ $key }} @endif" class="progress-icon p-10 d-flex align-items-center justify-content-center rounded-circle {{ $key == $currentStep ? 'active' : '' }}" data-toggle="tooltip" data-placement="top" title="{{ trans('public.' . $step['name']) }}">
                {{-- <img src="/assets/default/img/icons/{{ $step['icon'] }}.svg" class="img-cover" alt=""> --}}
                @if (file_exists(public_path()."/assets/default/img/icons/progress/{$progressItemsCount}.png"))
                    <img src="/assets/default/img/icons/progress/{{ $progressItemsCount }}.png" class="img-cover" alt="">
                @else
                    <img src="/assets/default/img/icons/{{ $step['icon'] }}.svg" class="img-cover" alt="">
                @endif
            </a>
            @php    $progressItemsCount++; @endphp
            <div class="ml-10 {{ $key == $currentStep ? '' : 'd-lg-none' }}">
                <h4 class="font-16 text-secondary font-weight-bold">{{ trans('public.' . $step['name']) }}</h4>
            </div>
        </div>
    @endforeach
</div>