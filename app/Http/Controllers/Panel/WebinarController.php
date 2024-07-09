<?php

namespace App\Http\Controllers\Panel;

use App\CourseVisibility;
use App\Exports\WebinarStudents;
use App\Http\Controllers\Controller;
use App\Mail\SendNotifications;
use App\Models\AuditTrail;
use App\Models\Category;
use App\Models\Faq;
use App\Models\File;
use App\Models\Prerequisite;
use App\Models\Quiz;
use App\Models\QuizzesQuestion;
use App\Models\QuizzesQuestionsAnswer;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Session;
use App\Models\Tag;
use App\Models\TextLesson;
use App\Models\TextLessonAttachment;
use App\Models\Ticket;
use App\Models\Translation\FileTranslation;
use App\Models\Translation\QuizTranslation;
use App\Models\Translation\QuizzesQuestionsAnswerTranslation;
use App\Models\Translation\QuizzesQuestionTranslation;
use App\Models\Translation\TextLessonTranslation;
use App\Models\Translation\WebinarChapterTranslation;
use App\Models\Translation\WebinarTranslation;
use App\Models\WebinarChapter;
use App\User;
use App\Models\Webinar;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class WebinarController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isUser()) {
            abort(404);
        }

        $query = Webinar::where(function ($query) use ($user) {
            if ($user->isTeacher()) {
                $query->where('teacher_id', $user->id);
            } elseif ($user->isOrganization()) {
                $query->where('creator_id', $user->id);
            }
        });

        $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
        $data['pageTitle'] = trans('webinars.webinars_list_page_title');

        return view(getTemplate() . '.panel.webinar.index', $data);
    }


    public function invitations(Request $request)
    {
        $user = auth()->user();

        $invitedWebinarIds = WebinarPartnerTeacher::where('teacher_id', $user->id)->pluck('webinar_id')->toArray();

        $query = Webinar::where('status', 'active');

        if ($user->isUser()) {
            abort(404);
        }

        $query->whereIn('id', $invitedWebinarIds);

        $data = $this->makeMyClassAndInvitationsData($query, $user, $request);
        $data['pageTitle'] = trans('panel.invited_classes');

        return view(getTemplate() . '.panel.webinar.index', $data);
    }

    public function organizationClasses(Request $request)
    {
        $user = auth()->user();

        if (!empty($user->organ_id)) {
            $query = Webinar::where('creator_id', $user->organ_id)
                ->where('status', 'active');

            $query = $this->organizationClassesFilters($query, $request);

            $webinars = $query
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('panel.organization_classes'),
                'webinars' => $webinars,
            ];

            return view(getTemplate() . '.panel.webinar.organization_classes', $data);
        }

        abort(404);
    }

    private function organizationClassesFilters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $type = $request->get('type', null);
        $sort = $request->get('sort', null);
        $free = $request->get('free', null);

        $query = fromAndToDateFilter($from, $to, $query, 'start_date');

        if (!empty($type) and $type != 'all') {
            $query->where('type', $type);
        }

        if (!empty($sort) and $sort != 'all') {
            if ($sort == 'expensive') {
                $query->orderBy('price', 'desc');
            }

            if ($sort == 'inexpensive') {
                $query->orderBy('price', 'asc');
            }

            if ($sort == 'bestsellers') {
                $query->whereHas('sales')
                    ->with('sales')
                    ->get()
                    ->sortBy(function ($qu) {
                        return $qu->sales->count();
                    });
            }

            if ($sort == 'best_rates') {
                $query->with([
                    'reviews' => function ($query) {
                        $query->where('status', 'active');
                    }
                ])->get()
                    ->sortBy(function ($qu) {
                        return $qu->reviews->avg('rates');
                    });
            }
        }

        if (!empty($free) and $free == 'on') {
            $query->where(function ($qu) {
                $qu->whereNull('price')
                    ->orWhere('price', '<', '0');
            });
        }

        return $query;
    }

    private function makeMyClassAndInvitationsData($query, $user, $request)
    {
        $webinarHours = deepClone($query)->sum('duration');

        $onlyNotConducted = $request->get('not_conducted');
        if (!empty($onlyNotConducted)) {
            $query->where('status', 'active')
                ->where('start_date', '>', time());
        }

        $query->with([
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'category',
            'teacher',
            'sales' => function ($query) {
                $query->where('type', 'webinar')
                    ->whereNull('refund_at');
            }
        ])->orderBy('updated_at', 'desc');

        $webinarsCount = $query->count();

        $webinars = $query->paginate(10);

        $webinarSales = Sale::where('seller_id', $user->id)
            ->where('type', 'webinar')
            ->whereNotNull('webinar_id')
            ->whereNull('refund_at')
            ->with('webinar')
            ->get();

        $webinarSalesAmount = 0;
        $courseSalesAmount = 0;
        foreach ($webinarSales as $webinarSale) {
            if ($webinarSale->webinar->type == 'webinar') {
                $webinarSalesAmount += $webinarSale->amount;
            } else {
                $courseSalesAmount += $webinarSale->amount;
            }
        }

        return [
            'webinars' => $webinars,
            'webinarsCount' => $webinarsCount,
            'webinarSalesAmount' => $webinarSalesAmount,
            'courseSalesAmount' => $courseSalesAmount,
            'webinarHours' => $webinarHours,
        ];
    }

    function array_replace_key($search, $replace, array $subject)
    {
        $updatedArray = [];

        foreach ($subject as $key => $value) {
            if (!is_array($value) && $key == $search) {
                $updatedArray = array_merge($updatedArray, [$replace => $value]);

                continue;
            }

            $updatedArray = array_merge($updatedArray, [$key => $value]);
        }

        return $updatedArray;
    }

    public function getUserLanguagesLists()
    {
        $generalSettings = getGeneralSettings();
        $userLanguages = $generalSettings ? $generalSettings['user_languages'] : null;

        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        if (count($userLanguages) > 0) {
            foreach ($userLanguages as $locale => $language) {
                if (mb_strtolower($locale) == mb_strtolower(app()->getLocale())) {
                    $firstKey = array_key_first($userLanguages);

                    if ($firstKey != $locale) {
                        $firstValue = $userLanguages[$firstKey];

                        unset($userLanguages[$locale]);
                        unset($userLanguages[$firstKey]);

                        $userLanguages = array_merge([
                            $locale => $language,
                            $firstKey => $firstValue
                        ], $userLanguages);
                    }
                }
            }
        }

        return $userLanguages;
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $teachers = null;
        $isOrganization = $user->isOrganization();

        if ($isOrganization) {
            $teachers = User::where('role_name', Role::$teacher)
                ->where('organ_id', $user->id)->get();
        }
        $query = User::where('role_name', Role::$organization)->where('status', 'active')->get();

        $data = [
            'pageTitle' => trans('webinars.new_page_title'),
            'teachers' => $teachers,
            'categories' => $categories,
            'isOrganization' => $isOrganization,
            'currentStep' => 1,
            'userLanguages' => $this->getUserLanguagesLists(),
            'organization' => $query,
        ];

        return view(getTemplate() . '.panel.webinar.create', $data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $currentStep = $request->get('current_step', 1);

        $rules = [
            'type' => 'required|in:webinar,course,text_lesson',
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'image_cover' => 'required',
            'description' => 'required',
        ];

        if (!$user->isTeacher()) {
            $rules['teacher_id'] = 'required';
        }

        $this->validate($request, $rules);

        $data = $request->all();

        $webinar = Webinar::create([
            'teacher_id' => $user->isTeacher() ? $user->id : $data['teacher_id'],
            'creator_id' => $user->id,
            'slug' => Webinar::makeSlug($data['title']),
            'type' => $data['type'],
            'visible_to_all' => isset($data['visible_to_all']) ? true : false,
            'private' => (!empty($data['private']) and $data['private'] == 'on') ? true : false,
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'],
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? Webinar::$isDraft : Webinar::$pending,
            'created_at' => time(),
        ]);

        if ($webinar) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);

            if( !$request->has("visible_to_all") && $request->has("organizations")) {
                $organizations = $request->organizations;
                foreach ( $organizations as $organizationId ) {
                    CourseVisibility::create([
                        "course_id" => $webinar->id,
                        "organization_id" => $organizationId,
                    ]);
                }
            }
        }

        $url = '/panel/webinars';
        if ($data['get_next'] == 1) {
            $url = '/panel/webinars/' . $webinar->id . '/step/2';
        }

        return redirect($url);
    }

    public function edit(Request $request, $id, $step = 1)
    {
        $user = auth()->user();
        $isOrganization = $user->isOrganization();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }
        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('webinars.new_page_title_step', ['step' => $step]),
            'currentStep' => $step,
            'isOrganization' => $isOrganization,
            'userLanguages' => $this->getUserLanguagesLists(),
            'locale' => mb_strtolower($locale),
            'defaultLocale' => getDefaultLocale(),
        ];

        $query = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            });

        if ($step == '1') {
            $data['teachers'] = $user->getOrganizationTeachers()->get();
        } elseif ($step == 2) {
            $query->with([
                'category' => function ($query) {
                    $query->with(['filters' => function ($query) {
                        $query->with('options');
                    }]);
                },
                'filterOptions',
                'webinarPartnerTeacher' => function ($query) {
                    $query->with(['teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    }]);
                },
                'tags',
            ]);

            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $data['categories'] = $categories;
        } elseif ($step == 3) {
            $query->with([
                'tickets' => function ($query) {
                    $query->orderBy('order', 'desc');
                },
            ]);
        } elseif ($step == 4) {
            $query->with([
                'quizzes',
                'chapters' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc');
                    $query->with([
                        'quizzes' => function ($query) {
                            $query->where('text_lesson_id', null);
                        },
                        'files' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'sessions' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'textLessons' => function ($query) {
                            $query->with([
                                'quizzes',
                                'attachments' => function ($qu) {
                                    $qu->with('file');
                                }
                            ])->orderBy('order', 'asc');
                        }
                    ]);
                },
            ]);
        } elseif ($step == 5) {
            $query->with([
                'prerequisites' => function ($query) {
                    $query->with(['prerequisiteWebinar' => function ($qu) {
                        $qu->with(['teacher' => function ($q) {
                            $q->select('id', 'full_name');
                        }]);
                    }])->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 6) {
            $query->with([
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ]);
        } elseif ($step == 7) {
            $query->with([
                'quizzes',
                'chapters' => function ($query) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc');
                }
            ]);

            $teacherQuizzes = Quiz::where('webinar_id', null)
                ->where('creator_id', $user->id)
                ->whereNull('webinar_id')
                ->get();

            $data['teacherQuizzes'] = $teacherQuizzes;
        }


        $webinar = $query->first();

        $organizationQuery = User::where('role_name', Role::$organization)->where('status', 'active')->get();
        $data["organization"] = $organizationQuery;

        $courseVisibility = CourseVisibility::where("course_id", $webinar->id)->pluck("organization_id")->toArray();
        $data["courseVisibility"] = $courseVisibility;

        if (empty($webinar)) {
            abort(404);
        }

        $data['webinar'] = $webinar;

        $data['pageTitle'] = trans('public.edit') . ' ' . $webinar->title;

        $definedLanguage = [];
        if ($webinar->translations) {
            $definedLanguage = $webinar->translations->pluck('locale')->toArray();
        }

        $data['definedLanguage'] = $definedLanguage;

        if ($step == 2) {
            $data['webinarTags'] = $webinar->tags->pluck('title')->toArray();

            $webinarCategoryFilters = !empty($webinar->category) ? $webinar->category->filters : [];

            if (empty($webinar->category) and !empty($request->old('category_id'))) {
                $category = Category::where('id', $request->old('category_id'))->first();

                if (!empty($category)) {
                    $webinarCategoryFilters = $category->filters;
                }
            }

            $data['webinarCategoryFilters'] = $webinarCategoryFilters;
        }

        if ($step == 3) {
            $data['sumTicketsCapacities'] = $webinar->tickets->sum('capacity');
        }


        return view(getTemplate() . '.panel.webinar.create', $data);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $rules = [];
        $data = $request->except('action_button');
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = !empty($data['get_next'] and $data['get_next'] == 1) ? true : false;
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (empty($webinar)) {
            abort(404);
        }

        if ($currentStep == 1) {
            $rules = [
                'type' => 'required|in:webinar,course,text_lesson',
                'title' => 'required|max:255',
                'thumbnail' => 'required',
                'image_cover' => 'required',
                'description' => 'required',
            ];
        }

        if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required',
                'duration' => 'required',
                'partners' => 'required_if:partner_instructor,on',
            ];

            if ($webinar->isWebinar()) {
                $rules['start_date'] = 'required|date';
                $rules['capacity'] = 'required|integer';
            }
        }

        $webinarRulesRequired = false;
        if (($currentStep == 8 and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft)) {
            $webinarRulesRequired = empty($data['rules']);
        }

        $this->validate($request, $rules);


        $data['status'] = ($isDraft or $webinarRulesRequired) ? Webinar::$isDraft : Webinar::$pending;
        $data['updated_at'] = time();

        if ($currentStep == 1) {
            $data["visible_to_all"] = isset($data["visible_to_all"]) ? true : false;
            $data['private'] = (!empty($data['private']) and $data['private'] == 'on');
            if( $webinar && !$request->has("visible_to_all") && $request->has("organizations")) {
                CourseVisibility::where("course_id", $webinar->id)->delete();
                $organizations = $request->organizations;
                foreach ( $organizations as $organizationId ) {
                    CourseVisibility::create([
                        "course_id" => $webinar->id,
                        "organization_id" => $organizationId,
                    ]);
                }
            } else {
                CourseVisibility::where("course_id", $webinar->id)->delete();
            }
        }

        if ($currentStep == 2) {
            if ($webinar->type == 'webinar') {
                $data['start_date'] = strtotime($data['start_date']);
            }

            $data['support'] = !empty($data['support']) ? true : false;
            $data['downloadable'] = !empty($data['downloadable']) ? true : false;
            $data['partner_instructor'] = !empty($data['partner_instructor']) ? true : false;

            if (empty($data['partner_instructor'])) {
                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                unset($data['partners']);
            }

            if ($data['category_id'] !== $webinar->category_id) {
                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            }
        }

        if ($currentStep == 3) {
            $data['subscribe'] = !empty($data['subscribe']) ? true : false;
        }

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
            foreach ($filters as $filter) {
                WebinarFilterOption::create([
                    'webinar_id' => $webinar->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));
            Tag::where('webinar_id', $webinar->id)->delete();

            foreach ($tags as $tag) {
                Tag::create([
                    'webinar_id' => $webinar->id,
                    'title' => $tag,
                ]);
            }
        }

        if (!empty($request->get('partner_instructor')) and !empty($request->get('partners'))) {
            WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();

            foreach ($request->get('partners') as $partnerId) {
                WebinarPartnerTeacher::create([
                    'webinar_id' => $webinar->id,
                    'teacher_id' => $partnerId,
                ]);
            }
        }

        if ($webinar and $currentStep == 1) {
            WebinarTranslation::updateOrCreate([
                'webinar_id' => $webinar->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
                'seo_description' => $data['seo_description'],
            ]);
        }

        unset(
            $data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_next'],
            $data['partners'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['title'],
            $data['description'],
            $data['seo_description'],
        );
        // dont revert to draft/pending once published/active
        // if intentionally setting as draft/pending then do nothing keep going as previously implemented
        if(!in_array($request->get('action_button'),["saveAsDraft","sendForReview"])){
            if($isDraft && $webinar->status == Webinar::$active){
                $data["status"] = $webinar->status;
            }
        }

        if($request->get('action_button') == "sendForReview") {
            $data["status"] = Webinar::$active;
        }

        $webinar->update($data);

        $url = '/panel/webinars';
        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/webinars/' . $webinar->id . '/step/' . (($nextStep <= 8) ? $nextStep : 8);
        }

        if ($webinarRulesRequired) {
            $url = '/panel/webinars/' . $webinar->id . '/step/8';

            return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
        }

        if (!$getNextStep and !$isDraft and !$webinarRulesRequired) {
            sendNotification('course_created', ['[c.title]' => $webinar->title], $user->id);
        }

        return redirect($url);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();

        // if (!$user->isTeacher() and !$user->isOrganization()) {
        //     abort(404);
        // }
        if (!$user->isAdmin()) {
            abort(403);
        }

        $webinar = Webinar::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!$webinar) {
            abort(404);
        }

        $webinar->delete();

        return response()->json([
            'code' => 200,
            'redirect_to' => $request->get('redirect_to')
        ], 200);
    }

    public function duplicate($id)
    {
        $user = auth()->user();
        if (!$user->isTeacher() and !$user->isOrganization() and !$user->isAdmin()) {
            abort(403);
        }

        //Get chapters against source course
        $chapters = WebinarChapter::where('webinar_id', $id)->get()->toArray();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })
            ->first();

        if (!empty($webinar)) {
            $new = $webinar->toArray();

            $title = $webinar->title . ' ' . trans('public.copy');
            $description = $webinar->description;
            $seo_description = $webinar->seo_description;


            $new['created_at'] = time();
            $new['updated_at'] = time();
            $new['status'] = Webinar::$pending;

            $new['slug'] = Webinar::makeSlug($title);

            foreach ($webinar->translatedAttributes as $attribute) {
                unset($new[$attribute]);
            }

            unset($new['translations']);

            $newWebinar = Webinar::create($new);

            WebinarTranslation::updateOrCreate([
                'webinar_id' => $newWebinar->id,
                'locale' => mb_strtolower($webinar->locale),
            ], [
                'title' => $title,
                'description' => $description,
                'seo_description' => $seo_description,
            ]);

            //Get chapters against source course
            $chapters = WebinarChapter::where('webinar_id', $id)->with(['textLessons', 'quizzes'])->get();
            if ($chapters->count() > 0) {
                $creationTime = time();
                $chapterMappings = [];
                foreach ($chapters as $chapter) {
                    $cloneData = $chapter->toArray();
                    $cloneData['webinar_id'] = $newWebinar->id;
                    $cloneData['created_at'] = $creationTime;
                    unset($cloneData['id']);
                    $clonedChapter = WebinarChapter::Create($cloneData);
                    $chapterMappings[$chapter->id] = $clonedChapter->id;
                }

                //Now let's insert the translations
                $oldChapterIds = array_keys($chapterMappings);
                $chapterTranslations = WebinarChapterTranslation::whereIn('webinar_chapter_id', $oldChapterIds)->get();
                if ($chapterTranslations->count() > 0) {
                    $chapterTranslationsData = [];
                    foreach ($chapterTranslations as $chapterTranslation) {
                        $translation = $chapterTranslation->toArray();
                        unset($translation['id']);
                        $oldChapterId = $translation['webinar_chapter_id'];
                        $translation['webinar_chapter_id'] = $chapterMappings[$oldChapterId];
                        $chapterTranslationsData[] = $translation;
                    }

                    //bulk insertion of translations
                    WebinarChapterTranslation::Insert($chapterTranslationsData);
                }

                //Now that chapters are inserted, let's add text lessons
                if (isset($oldChapterIds) && count($oldChapterIds) > 0) {
                    $textLessons = TextLesson::whereIn('chapter_id', $oldChapterIds)->get();
                    if ($textLessons->count() > 0) {
                        $textLessonMappings = [];
                        foreach ($textLessons as $textlesson) {
                            $newTextLesson = $textlesson->toArray();
                            unset($newTextLesson['id']);
                            $newTextLesson['created_at'] = $creationTime;
                            $newTextLesson['updated_at'] = null;
                            $newTextLesson['webinar_id'] = $newWebinar->id;
                            $newTextLesson['chapter_id'] = $chapterMappings[$textlesson->chapter_id];
                            $clonedTextLesson = TextLesson::Create($newTextLesson);
                            $textLessonMappings[$textlesson->id] = $clonedTextLesson->id;
                        }
                        $oldTextLessonIds = array_keys($textLessonMappings);

                        //Insert Text Lesson Translations
                        $textLessonTranslations = TextLessonTranslation::whereIn('text_lesson_id', $oldTextLessonIds)->get();
                        if ($textLessonTranslations->count() > 0) {
                            $textLessonsTranslationData = [];
                            foreach ($textLessonTranslations as $textLessonTranslation) {
                                $translation = $textLessonTranslation->toArray();
                                unset($translation['id']); //unset the old id
                                $oldTextLessonId = $translation['text_lesson_id'];
                                $translation['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
                                $textLessonsTranslationData[] = $translation;
                            }
                            //bulk insertion of translations
                            TextLessonTranslation::Insert($textLessonsTranslationData);
                        }

                        //File Attachments
                        $files = File::whereIn('chapter_id', $oldChapterIds)->get();
                        if ($files->count() > 0) {
                            $fileMappings = [];
                            foreach ($files as $file) {
                                $newFile = $file->toArray();
                                unset($newFile['id']);
                                $newFile['webinar_id'] = $newWebinar->id;
                                $oldChapterId = $newFile['chapter_id'];
                                $newFile['chapter_id'] = $chapterMappings[$oldChapterId];
                                $insertedFile = File::create($newFile);
                                $fileMappings[$file->id] = $insertedFile->id;
                            }
                            //File Translations
                            $oldFileIds = array_keys($fileMappings);
                            $oldFileTranslations = FileTranslation::whereIn('file_id', $oldFileIds)->get();
                            if ($oldFileTranslations->count() > 0) {
                                $fileTranslationsData = [];
                                foreach ($oldFileTranslations as $oldFileTranslation) {
                                    $fileTranslation = $oldFileTranslation->toArray();
                                    unset($fileTranslation['id']);
                                    $oldFileId = $fileTranslation['file_id'];
                                    $fileTranslation['file_id'] = $fileMappings[$oldFileId];
                                    $fileTranslationsData[] = $fileTranslation;
                                }
                                FileTranslation::Insert($fileTranslationsData);
                            }

                            //Text Lesson Attachments
                            $lessonAttachments = TextLessonAttachment::whereIn('text_lesson_id', $oldChapterIds)->get();
                            if ($lessonAttachments->count() > 0) {
                                $textLessonAttachmentData = [];
                                foreach ($lessonAttachments as $lessonAttachment) {
                                    $attachment = $lessonAttachment->toArray();
                                    unset($attachment['id']);
                                    $attachment['created_at'] = $creationTime;
                                    $oldTextLessonId = $attachment['text_lesson_id'];
                                    $attachment['text_lesson_id'] = $textLessonMappings[$oldTextLessonId];
                                    $oldFileId = $attachment['file_id'];
                                    $attachment['file_id'] = $fileMappings[$oldFileId];
                                    $textLessonAttachmentData[] = $attachment;
                                }
                                TextLessonAttachment::Insert($textLessonAttachmentData);
                            }
                        }
                    }
                }

                //Clone Quizzes
                $oldQuizzes = Quiz::whereIn('chapter_id', $oldChapterIds)->get();
                if ($oldQuizzes->count() > 0) {
                    $quizMappings = [];
                    foreach ($oldQuizzes as $oldQuiz) {
                        $quiz = $oldQuiz->toArray();
                        unset($quiz['id']);
                        $quiz['created_at'] = $creationTime;
                        $quiz['updated_at'] = null;
                        $oldChapterId = $quiz['chapter_id'];
                        $quiz['chapter_id'] = $chapterMappings[$oldChapterId];
                        $quiz['webinar_id'] = $newWebinar->id;
                        $newQuiz = Quiz::Create($quiz);
                        $quizMappings[$oldQuiz->id] = $newQuiz->id;
                    }
                    $oldQuizIds = array_keys($quizMappings);

                    //Quiz Translations
                    $oldQuizTranslations = QuizTranslation::whereIn('quiz_id', $oldQuizIds)->get();
                    if ($oldQuizTranslations->count() > 0) {
                        $quizTranslationData = [];
                        foreach ($oldQuizTranslations as $oldQuizTranslation) {
                            $quizTranslation = $oldQuizTranslation->toArray();
                            unset($quizTranslation['id']);
                            $oldQuizId = $quizTranslation['quiz_id'];
                            $quizTranslation['quiz_id'] = $quizMappings[$oldQuizId];
                            $quizTranslationData[] = $quizTranslation;
                        }
                        QuizTranslation::Insert($quizTranslationData);
                    }

                    //Quiz Questions
                    $oldQuizQuestions = QuizzesQuestion::whereIn('quiz_id', $oldQuizIds)->get();
                    if ($oldQuizQuestions->count() > 0) {
                        $quizQuestionMappings = [];
                        foreach ($oldQuizQuestions as $oldQuizQuestion) {
                            $quizQuestion = $oldQuizQuestion->toArray();
                            unset($quizQuestion['id']);
                            $oldQuizId = $quizQuestion['quiz_id'];
                            $quizQuestion['quiz_id'] = $quizMappings[$oldQuizId];
                            $quizQuestion['created_at'] = $creationTime;
                            $quizQuestion['updated_at'] = null;
                            $newQuizQuestion = QuizzesQuestion::Create($quizQuestion);
                            $quizQuestionMappings[$oldQuizQuestion->id] = $newQuizQuestion->id;
                        }

                        //Quiz Question Translations
                        $oldQuizQuestionIds = array_keys($quizQuestionMappings);
                        $oldQuizQuestionTranslations = QuizzesQuestionTranslation::whereIn('quizzes_question_id', $oldQuizQuestionIds)->get();
                        if ($oldQuizQuestionTranslations->count() > 0) {
                            $questionTranslationsData = [];
                            foreach ($oldQuizQuestionTranslations as $oldQuizQuestionTranslation) {
                                $questionTranslation = $oldQuizQuestionTranslation->toArray();
                                unset($questionTranslation['id']);
                                $oldQuizQuestionId = $questionTranslation['quizzes_question_id'];
                                $questionTranslation['quizzes_question_id'] = $quizQuestionMappings[$oldQuizQuestionId];
                                $questionTranslationsData[] = $questionTranslation;
                            }
                            QuizzesQuestionTranslation::Insert($questionTranslationsData);
                        }

                        //Quiz Question Answers
                        $oldQuestionAnswers = QuizzesQuestionsAnswer::whereIn('question_id', $oldQuizQuestionIds)->get();
                        if ($oldQuestionAnswers->count() > 0) {
                            $questionAnswerMappings = [];
                            foreach ($oldQuestionAnswers as $oldQuestionAnswer) {
                                $questionAnswer = $oldQuestionAnswer->toArray();
                                unset($questionAnswer['id']);
                                $oldQuestionId = $questionAnswer['question_id'];
                                $questionAnswer['question_id'] = $quizQuestionMappings[$oldQuestionId];
                                $questionAnswer['created_at'] = $creationTime;
                                $quizQuestionAnswer = QuizzesQuestionsAnswer::Create($questionAnswer);
                                $questionAnswerMappings[$oldQuestionAnswer->question_id] = $quizQuestionAnswer->id;
                            }

                            //Quiz Question Answer Translations
                            $oldQuestionAnswerIds = array_keys($questionAnswerMappings);
                            $oldAnswerTranslations = QuizzesQuestionsAnswerTranslation::whereIn('quizzes_questions_answer_id', $oldQuestionAnswerIds)->get();
                            if ($oldAnswerTranslations->count() > 0) {
                                $answerTranslationsData = [];
                                foreach ($oldAnswerTranslations as $oldAnswerTranslation) {
                                    $answerTranslation = $oldAnswerTranslation->toArray();
                                    unset($answerTranslation['id']);
                                    $oldQuestionAnswerId = $answerTranslation['quizzes_questions_answer_id'];
                                    $answerTranslation['quizzes_questions_answer_id'] = $questionAnswerMappings[$oldQuestionAnswerId];
                                    $answerTranslationsData[] = $answerTranslation;
                                }
                                QuizzesQuestionsAnswerTranslation::Insert($answerTranslationsData);
                            }
                        }
                    }
                }
            }

            if (isset($newWebinar->id)) {
                //Audit Trail entry - course duplicated
                $audit = new AuditTrail();
                $audit->user_id = $user->id;
                $audit->organ_id = $user->organ_id;
                $audit->role_name = $user->role_name;
                $audit->audit_type = AuditTrail::auditType['course_duplicated'];
                $audit->added_by = $user->id;
                $audit->description = "User {$user->full_name} ({$user->id}) cloned existing course ({$id}). New Course id: {$newWebinar->id}";
                $ip = null;
                $ip = getClientIp();
                $audit->ip = ip2long($ip);
                $audit->save();
            }

            return redirect('/panel/webinars/' . $newWebinar->id . '/edit');
        }

        abort(404);
    }

    public function exportStudentsList($id)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            abort(404);
        }

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })
            ->first();

        if (!empty($webinar)) {
            $sales = Sale::where('type', 'webinar')
                ->where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->with([
                    'buyer' => function ($query) {
                        $query->select('id', 'full_name', 'email', 'mobile');
                    }
                ])->get();

            if (!empty($sales) and !$sales->isEmpty()) {
                $export = new WebinarStudents($sales);
                return Excel::download($export, trans('panel.users') . '.xlsx');
            }

            $toastData = [
                'title' => trans('public.request_failed'),
                'msg' => trans('webinars.export_list_error_not_student'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function search(Request $request)
    {
        $user = auth()->user();

        if (!$user->isTeacher() and !$user->isOrganization()) {
            return response('', 422);
        }

        $term = $request->get('term', null);
        $webinarId = $request->get('webinar_id', null);
        $option = $request->get('option', null);

        if (!empty($term)) {
            $webinars = Webinar::select('id', 'teacher_id')
                ->whereTranslationLike('title', '%' . $term . '%')
                ->where('id', '<>', $webinarId)
                ->with(['teacher' => function ($query) {
                    $query->select('id', 'full_name');
                }])
                //->where('creator_id', $user->id)
                ->get();

            foreach ($webinars as $webinar) {
                $webinar->title .= ' - ' . $webinar->teacher->full_name;
            }
            return response()->json($webinars, 200);
        }

        return response('', 422);
    }

    public function getTags(Request $request, $id)
    {
        $webinarId = $request->get('webinar_id', null);

        if (!empty($webinarId)) {
            $tags = Tag::select('id', 'title')
                ->where('webinar_id', $webinarId)
                ->get();

            return response()->json($tags, 200);
        }

        return response('', 422);
    }

    public function invoice($id)
    {
        $user = auth()->user();

        $sale = Sale::where('buyer_id', $user->id)
            ->where('webinar_id', $id)
            ->where('type', 'webinar')
            ->whereNull('refund_at')
            ->with([
                'order',
                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->first();

        if (!empty($sale)) {
            $webinar = Webinar::where('status', 'active')
                ->where('id', $id)
                ->with([
                    'teacher' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'creator' => function ($query) {
                        $query->select('id', 'full_name');
                    },
                    'webinarPartnerTeacher' => function ($query) {
                        $query->with([
                            'teacher' => function ($query) {
                                $query->select('id', 'full_name');
                            },
                        ]);
                    }
                ])
                ->first();

            if (!empty($webinar)) {
                $data = [
                    'pageTitle' => trans('webinars.invoice_page_title'),
                    'sale' => $sale,
                    'webinar' => $webinar
                ];

                return view(getTemplate() . '.panel.webinar.invoice', $data);
            }
        }

        abort(404);
    }

    public function purchases(Request $request)
    {
        /* $requestedUser = $request->get('fetchForUser');
        if (isset($requestedUser) and (int)$requestedUser > 0) {
            $user = User::find((int)$requestedUser);
        } else {
            $user = auth()->user();
        } */
        $user = auth()->user();
        $webinarIds = $user->getPurchasedCoursesIds();

        $query = Webinar::whereIn('id', $webinarIds);
        if (auth()->user()->isUser()) {
            $query = $query->where("status", "active");
        }

        $allWebinars = deepClone($query)->get();
        $allWebinarsCount = $allWebinars->count();
        $hours = $allWebinars->sum('duration');

        $upComing = 0;
        $time = time();

        foreach ($allWebinars as $webinar) {
            if (!empty($webinar->start_date) and $webinar->start_date > $time) {
                $upComing += 1;
            }
        }

        $onlyNotConducted = $request->get('not_conducted');
        if (!empty($onlyNotConducted)) {
            $query->where('start_date', '>', time());
        }

        $webinars = $query->with([
            'files',
            'reviews' => function ($query) {
                $query->where('status', 'active');
            },
            'category',
            'teacher' => function ($query) {
                $query->select('id', 'full_name');
            },
        ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        foreach ($webinars as $webinar) {
            $sale = Sale::where('buyer_id', $user->id)
                ->whereNotNull('webinar_id')
                ->where('type', 'webinar')
                ->where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->first();

            if (!empty($sale)) {
                $webinar->purchast_date = $sale->created_at;
            }
        }

        $data = [
            'pageTitle' => trans('webinars.webinars_purchases_page_title'),
            'webinars' => $webinars,
            'allWebinarsCount' => $allWebinarsCount,
            'hours' => $hours,
            'upComing' => $upComing
        ];

        return view(getTemplate() . '.panel.webinar.purchases', $data);
    }

    public function getJoinInfo(Request $request)
    {
        $data = $request->all();
        if (!empty($data['webinar_id'])) {
            $user = auth()->user();

            $checkSale = Sale::where('buyer_id', $user->id)
                ->where('webinar_id', $data['webinar_id'])
                ->where('type', 'webinar')
                ->whereNull('refund_at')
                ->first();

            if (!empty($checkSale)) {
                $webinar = Webinar::where('status', 'active')
                    ->where('id', $data['webinar_id'])
                    ->first();

                if (!empty($webinar)) {
                    $session = Session::select('id', 'creator_id', 'date', 'link', 'zoom_start_link', 'session_api', 'api_secret')
                        ->where('webinar_id', $webinar->id)
                        ->where('date', '>=', time())
                        ->orderBy('date', 'asc')
                        ->first();

                    if (!empty($session)) {
                        $session->date = dateTimeFormat($session->date, 'd F Y , H:i');

                        $session->link = $session->getJoinLink(true);

                        return response()->json([
                            'code' => 200,
                            'session' => $session
                        ], 200);
                    }
                }
            }
        }

        return response()->json([], 422);
    }

    public function getNextSessionInfo($id)
    {
        $user = auth()->user();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (!empty($webinar)) {
            $session = Session::where('webinar_id', $webinar->id)
                ->where('date', '>=', time())
                ->orderBy('date', 'asc')
                ->first();

            if (!empty($session)) {
                $session->date = dateTimeFormat($session->date, 'Y-m-d H:i');

                $session->link = $session->getJoinLink(true);
            }

            return response()->json([
                'code' => 200,
                'session' => $session,
                'webinar_id' => $webinar->id,
            ], 200);
        }

        return response()->json([], 422);
    }

    public function orderItems(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'items' => 'required',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tableName = $data['table'];
        $itemIds = explode(',', $data['items']);

        if (!is_array($itemIds) and !empty($itemIds)) {
            $itemIds = [$itemIds];
        }

        if (!empty($itemIds) and is_array($itemIds) and count($itemIds)) {
            switch ($tableName) {
                case 'tickets':
                    foreach ($itemIds as $order => $id) {
                        Ticket::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'sessions':
                    foreach ($itemIds as $order => $id) {
                        Session::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'files':
                    foreach ($itemIds as $order => $id) {
                        File::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'text_lessons':
                    foreach ($itemIds as $order => $id) {
                        // echo $id . " - " . $order . ", ";
                        // echo auth()->user()->id . " - " . $user->id . " ";
                        TextLesson::where('id', $id)
                            // ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'prerequisites':
                    $webinarIds = $user->webinars()->pluck('id')->toArray();

                    foreach ($itemIds as $order => $id) {
                        Prerequisite::where('id', $id)
                            ->whereIn('webinar_id', $webinarIds)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'faqs':
                    foreach ($itemIds as $order => $id) {
                        Faq::where('id', $id)
                            ->where('creator_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
                case 'webinar_chapters':
                    foreach ($itemIds as $order => $id) {
                        WebinarChapter::where('id', $id)
                            ->where('user_id', $user->id)
                            ->update(['order' => ($order + 1)]);
                    }
                    break;
            }
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $webinar = Webinar::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhere('teacher_id', $user->id);
            })->first();

        if (!empty($webinar)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($webinar->$relation)) {
                $item = $webinar->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function processEnrolment(Request $request, $slug, $payment_type, $id)
    {
        $authUser = auth()->user();
        if (!$authUser->isOrganizationPersonnel()) {
            abort(403);
        }

        $user = User::where('id', $id)->first();

        if (empty($user) || $user->role_name !== Role::$user) {
            $toastData = [
                'title' => trans('panel.invalid_user'),
                'msg' => trans('panel.invalid_or_missing_user_reference'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();
        if (!in_array($payment_type, ['free', 'paid'])) {
            $toastData = [
                'title' => trans('panel.invalid_request'),
                'msg' => trans('panel.invalid_course_payment_type'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
        if (!empty($course)) {
            if (strtolower($payment_type) === 'free') {

                if (!empty($course->price) and $course->price > 0) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => trans('cart.course_not_free'),
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
                if (isset($alreadyPurchased->id)) {
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => 'Course already purchased',
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                Sale::create([
                    'buyer_id' => $id,
                    'seller_id' => $course->creator_id,
                    'webinar_id' => $course->id,
                    'type' => Sale::$webinar,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
                ]);

                $toastData = [
                    'title' => '',
                    'msg' => trans('cart.success_pay_msg_for_free_course'),
                    'status' => 'success'
                ];

                if (env('APP_ENV') == 'production') {
                    $title = $course->slug;
                    $message = trans('cart.success_pay_msg_for_free_course');
                    Mail::to($user->email)
                        ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                    Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
                }

                return back()->with(['toast' => $toastData]);
            } else {
                if (!empty($course->price) and $course->price > 0) {

                    $organizationId = null;
                    if ($authUser->isOrganization()) {
                        $organizationId = (int)$authUser->id;
                    } else {
                        $organizationId = (int)$authUser->organ_id;
                    }

                    //check whether the user (being requested for) is asociated with the current organization
                    if ((int)$user->organ_id !== $organizationId) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => trans('panel.student_not_of_current_organization'),
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
                    if (isset($alreadyPurchased->id)) {
                        $toastData = [
                            'title' => trans('cart.fail_purchase'),
                            'msg' => 'Course already purchased',
                            'status' => 'error'
                        ];
                        return back()->with(['toast' => $toastData]);
                    }

                    //1. Enter sale against organization
                    //create sale against organization
                    $price = $course->price;
                    $financialSettings = getFinancialSettings();
                    $taxPrice = 0;
                    if (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0 and $price > 0) {
                        $tax = $financialSettings['tax'];
                        $taxPrice = $price * $tax / 100;
                    }
                    $totalAmount = $price + $taxPrice;

                    $saleTime = time();
                    $organizationSale = Sale::create([
                        'buyer_id' => $organizationId,
                        'seller_id' => $course->creator_id,
                        'webinar_id' => $course->id,
                        'type' => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount' => $price,
                        'tax' => $taxPrice,
                        'total_amount' => $totalAmount,
                        'payment_status' => Sale::$paymentStatus['pending'],
                        'created_at' => $saleTime,
                        'paid_for' => $id,
                    ]);


                    //2. Enter sale against student
                    if (!empty($organizationSale->id)) {
                        //$auditedUser = User::find($break->user_id);
                        $ip = null;
                        $ip = getClientIp();
                        $ipLong = ip2long($ip);

                        $organizationUser = ($authUser->role_name === Role::$organization) ? $authUser : User::where('id', $organizationId)->first();
                        $auditMessage = "User {$authUser->full_name} ({$authUser->id}) enrolled student ({$user->full_name} [{$user->id}]) in course {$course->title} ({$course->id}) on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
                        $audit = new AuditTrail();
                        $audit->user_id = $authUser->id;
                        $audit->organ_id = $organizationId;
                        $audit->role_name = $authUser->role_name;
                        $audit->audit_type = AuditTrail::auditType['paid_course_purchase'];
                        $audit->added_by = null;
                        $audit->description = $auditMessage;
                        $audit->ip = $ipLong;
                        $audit->save();

                        $studentSale = Sale::create([
                            'buyer_id' => $id,
                            'seller_id' => $course->creator_id,
                            'webinar_id' => $course->id,
                            'type' => Sale::$webinar,
                            'payment_method' => Sale::$credit,
                            'amount' => $price,
                            'tax' => $taxPrice,
                            'discount' =>   $totalAmount,
                            'total_amount' => 0,
                            'created_at' => $saleTime,
                            'sale_reference_id' => $organizationSale->id,
                            'self_payed' => 0,
                        ]);

                        if (!empty($studentSale->id)) {

                            $auditMessage = "User {$user->full_name} ({$user->id}) enrolled in course {$course->title} ({$course->id}) by {$authUser->full_name} ({$authUser->id}), on behalf of organization '{$organizationUser->full_name} ($organizationId)'.";
                            $audit = new AuditTrail();
                            $audit->user_id = $user->id;
                            $audit->organ_id = $organizationId;
                            $audit->role_name = $user->role_name;
                            $audit->audit_type = AuditTrail::auditType['course_enrolment'];
                            $audit->added_by = $authUser->id;
                            $audit->description = $auditMessage;
                            $audit->ip = $ipLong;
                            $audit->save();

                            $toastData = [
                                'title' => '',
                                'msg' => trans('cart.success_pay_msg_for_course'),
                                'status' => 'success'
                            ];

                            if (env('APP_ENV') == 'production') {
                                $title = $course->slug;
                                $message = trans('cart.success_pay_msg_for_course');
                                Mail::to($user->email)
                                    ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                                Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
                            }

                            return back()->with(['toast' => $toastData]);
                        }
                    }
                }

                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.course_not_paid'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        }
        abort(404);
    }

    /* public function free(Request $request, $slug, $id)
    {
        $user = User::where('id', $id)->first();

        $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

        if (!empty($course)) {

            if (!empty($course->price) and $course->price > 0) {
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.course_not_free'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
            if (isset($alreadyPurchased->id)) {
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => 'Course already purchased',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            Sale::create([
                'buyer_id' => $id,
                'seller_id' => $course->creator_id,
                'webinar_id' => $course->id,
                'type' => Sale::$webinar,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'created_at' => time(),
            ]);

            $toastData = [
                'title' => '',
                'msg' => trans('cart.success_pay_msg_for_free_course'),
                'status' => 'success'
            ];

            if ( env('APP_ENV') == 'production' ) {
                $title = $course->slug;
                $message = trans('cart.success_pay_msg_for_free_course');
                Mail::to( $user->email )
                    ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
            }

            return back()->with(['toast' => $toastData]);
        }
        abort(404);
    }
    public function paid(Request $request, $slug, $id)
    {
        $user = User::where('id', $id)->first();

        $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

        if (!empty($course)) {

            if (!empty($course->price) and $course->price > 0) {
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.course_not_free'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            $alreadyPurchased = Sale::where('buyer_id', $id)->where('webinar_id', $course->id)->first();
            if (isset($alreadyPurchased->id)) {
                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => 'Course already purchased',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            Sale::create([
                'buyer_id' => $id,
                'seller_id' => $course->creator_id,
                'webinar_id' => $course->id,
                'type' => Sale::$webinar,
                'payment_method' => Sale::$credit,
                'amount' => 0,
                'total_amount' => 0,
                'created_at' => time(),
            ]);

            $toastData = [
                'title' => '',
                'msg' => trans('cart.success_pay_msg_for_free_course'),
                'status' => 'success'
            ];

            if ( env('APP_ENV') == 'production' ) {
                $title = $course->slug;
                $message = trans('cart.success_pay_msg_for_free_course');
                Mail::to( $user->email )
                    ->send(new SendNotifications(['title' => $title, 'message' => $message]));
                Log::channel('mail')->debug('Panel - Course Enroll Mail sent to : ' . $user->email);
            }

            return back()->with(['toast' => $toastData]);
        }
        abort(404);
    } */

    public function updateQuizRelation(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'quiz_id' => 'required',
            'chapter_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $lessonId = $data['lesson_id'] ?? null;
        $remvoeLesson = $data['remove_lesson'] ?? null;

        if ($lessonId) {
            $quiz = Quiz::where('id', $data['quiz_id'])->update(['chapter_id' => $data['chapter_id'], 'text_lesson_id' => $data['lesson_id']]);
        } else if ($remvoeLesson) {
            $quiz = Quiz::where('id', $data['quiz_id'])->update(['chapter_id' => $data['chapter_id'], 'text_lesson_id' => null]);
        } else {
            $quiz = Quiz::where('id', $data['quiz_id'])->update(['chapter_id' => $data['chapter_id']]);
        }

        return response()->json([], 200);
    }

    //function to get the chapters of a webinar
    public function getChapter ($webinarId)
    {
        $webinar = Webinar::find($webinarId);

        return response()->json($webinar->chapters,200);
    }
}
