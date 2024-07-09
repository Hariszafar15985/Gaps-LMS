<?php

namespace App\Helpers;

use App\Models\File;
use App\Models\Quiz;
use App\CustomSetting;
use App\Models\Webinar;
use App\CourseVisibility;
use App\Models\TextLesson;
use App\WebinarNotification;
use App\Models\CourseLearning;
use App\Models\QuizzesResult;
use App\Models\WebinarChapter;
use App\Models\WebinarChapterItem;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WebinarHelper
{
    public $user;
    public $webinar;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Function to prepare and return all course data with respect to a user.
     * @param String $slug
     * @param Model $user
     * @param bool $requestedForProfile
     * @return Model
     */
    public function getCourseDataBySlug($slug, $user, $requestedForProfile = false)
    {
        $course = Webinar::where('slug', $slug)
            ->with([
                'quizzes' => function ($query) {
                    $query->where('status', 'active')
                        ->with(['quizResults', 'quizQuestions']);
                },
                'tags',
                'prerequisites' => function ($query) {
                    $query->with(['prerequisiteWebinar' => function ($query) {
                        $query->with(['teacher' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }]);
                    }]);
                    $query->orderBy('order', 'asc');
                },
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'chapters' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive);
                    $query->orderBy('order', 'asc');

                    $query->with([
                        'quizzes' => function ($query) {
                            $query->where('status', 'active')
                                ->with(['quizResults', 'quizQuestions']);
                        },
                        'files' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    }
                                ]);
                        },
                        'textLessons' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->withCount(['attachments'])
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    },
                                    'quizzes' => function ($query) {
                                        $query->where('status', 'active');
                                    }
                                ]);
                        },
                        'sessions' => function ($query) use ($user) {
                            $query->where('status', WebinarChapter::$chapterActive)
                                ->orderBy('order', 'asc')
                                ->with([
                                    'learningStatus' => function ($query) use ($user) {
                                        $query->where('user_id', !empty($user) ? $user->id : null);
                                    }
                                ]);
                        },
                    ]);
                },
                'files' => function ($query) use ($user) {
                    $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                        ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                        ->where('files.status', WebinarChapter::$chapterActive)
                        ->orderBy('chapterOrder', 'asc')
                        ->orderBy('files.order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'textLessons' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->withCount(['attachments'])
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'sessions' => function ($query) use ($user) {
                    $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with([
                            'learningStatus' => function ($query) use ($user) {
                                $query->where('user_id', !empty($user) ? $user->id : null);
                            }
                        ]);
                },
                'tickets' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'filterOptions',
                'category',
                'teacher',
                'reviews' => function ($query) {
                    $query->where('status', 'active');
                    $query->with([
                        'comments',
                        'creator' => function ($qu) {
                            $qu->select('id', 'full_name', 'avatar');
                        }
                    ]);
                },
                'comments' => function ($query) {
                    $query->where('status', 'active');
                    $query->whereNull('reply_id');
                    $query->with([
                        'user' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar');
                        },
                        'replies' => function ($query) {
                            $query->where('status', 'active');
                            $query->with([
                                'user' => function ($query) {
                                    $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar');
                                }
                            ]);
                        }
                    ]);
                    $query->orderBy('created_at', 'desc');
                },
            ])
            ->withCount([
                'sales' => function ($query) {
                    $query->whereNull('refund_at');
                }
            ])
            ->where(function ($query) use ($requestedForProfile) {
                if (!$requestedForProfile) {
                    $query->where('status', 'active');
                }
            })
            ->first();
        return $course;
    }

    /**
     * Sets the Webinar property of the class to which the current course content belongs
     *
     * @param integer $id Id of the course content
     * @param string $contentType The type of course content (Webinar::$textLesson or Webinar::$quiz)
     * @return Webinar
     */
    public function getWebinarByContentId(int $id, string $contentType = 'text_lesson')
    {
        //default fallback value
        $webinar = null;

        if (in_array($contentType, [Webinar::$textLesson, 'text_lesson'])) {
            $lesson = TextLesson::find($id);
            if (!empty($lesson) && !empty($lesson->webinar_id)) {
                $webinar = Webinar::find($lesson->webinar_id);
            }
        } else if ($contentType === Webinar::$quiz) {
            $quiz = Quiz::find($id);
            if (!empty($quiz) && !empty($quiz->webinar_id)) {
                $webinar = Webinar::find($quiz->webinar_id);
            }
        }
        //return the webinar model object
        return $this->webinar = $webinar;
    }


    /**
     * Method to fetch the next Course Content (Text Lesson or Quiz/Assessment),
     * based on the current lesson/quiz
     *
     * @param integer $currentContentId
     * @param string $currentContentType
     * @return mixed
     */
    public function getNextContent(int $currentContentId, string $currentContentType)
    {
        if (empty($this->webinar)) {
            $this->getWebinarByContentId($currentContentId, $currentContentType);
        }
        if (!empty($this->webinar)) {
            if (in_array($currentContentType, [Webinar::$textLesson, 'text_lesson'])) {
                $currentLesson = TextLesson::find($currentContentId);
                if (!empty($currentLesson)) {
                    // 1. check if lesson has a quiz assigned to it
                    $quiz = Quiz::where([
                        'webinar_id' => $this->webinar->id,
                        'text_lesson_id' => $currentContentId,
                        'status' => Quiz::ACTIVE,
                    ])->first();
                    if (!empty($quiz)) {
                        if ($quiz->attemptable($this->user)) {
                            return [
                                'content_type' => Webinar::$quiz,
                                'content' => $quiz,
                                'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                            ];
                        } else {
                            return $this->getNextContent($quiz->id, Webinar::$quiz);
                        }
                    } else {
                        // 2. check if there is another lesson in the same chapter
                        $lesson = TextLesson::where([
                            'webinar_id' => $this->webinar->id,
                            'chapter_id' => $currentLesson->chapter_id,
                            'status' => TextLesson::$Active,
                        ])->where('order', '>=', $currentLesson->order)
                            ->where('id', '!=', $currentLesson->id)
                            ->orderBy('order')->orderBy('id')->first();
                        if (!empty($lesson)) {
                            if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                                return [
                                    'content_type' => Webinar::$textLesson,
                                    'content' => $lesson,
                                    'link' => route('web.lesson.read', [
                                        "slug" => $this->webinar->slug,
                                        "lesson_id" => $lesson->id
                                    ])
                                ];
                            } else {
                                return $this->getNextContent($lesson->id, Webinar::$textLesson);
                            }
                        } else {
                            // 3. check if there is a quiz in the same chapter not attached to any lesson
                            $quiz = Quiz::where([
                                'webinar_id' => $this->webinar->id,
                                'chapter_id' => $currentLesson->chapter_id,
                                'text_lesson_id' => null,
                                'status' => Quiz::ACTIVE,
                            ])->first();
                            if (!empty($quiz)) {
                                if ($quiz->attemptable($this->user)) {
                                    return [
                                        'content_type' => Webinar::$quiz,
                                        'content' => $quiz,
                                        'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                    ];
                                } else {
                                    return $this->getNextContent($quiz->id, Webinar::$quiz);
                                }
                            } else {
                                // 4. get first content in next available chapter
                                return $this->getNextChapterFirstContent($currentLesson->chapter_id);
                            }
                        }
                    }
                } else {
                    return null;
                }
            } else if ($currentContentType === Webinar::$quiz) {
                $currentQuiz = Quiz::find($currentContentId);
                // 1. Was quiz associated with any lesson?
                if (!empty($currentQuiz->text_lesson_id)) {
                    // 1.1 check if there is any other quiz associated with the parent lesson, besides itself
                    $quiz = Quiz::where([
                        'webinar_id' => $this->webinar->id,
                        'text_lesson_id' => $currentQuiz->text_lesson_id,
                        'status' => Quiz::ACTIVE,
                    ])->where('id', '>', $currentQuiz->id)->orderBy('id')->first();
                    if (!empty($quiz)) {
                        if ($quiz->attemptable($this->user)) {
                            return [
                                'content_type' => Webinar::$quiz,
                                'content' => $quiz,
                                'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                            ];
                        } else {
                            return $this->getNextContent($quiz->id, Webinar::$quiz);
                        }
                    } else {
                        // let's get the lesson as a reference now
                        $currentLesson = TextLesson::find($currentQuiz->text_lesson_id);
                        // 2. check the existing chapter for another lesson, if the quiz was associated with a lesson
                        $lesson = TextLesson::where([
                            'webinar_id' => $this->webinar->id,
                            'chapter_id' => $currentLesson->chapter_id,
                            'status' => TextLesson::$Active
                        ])->where('order', '>=', $currentLesson->order)
                            ->where('id', '!=', $currentLesson->id)
                            ->orderBy('order')->orderBy('id')->first();
                        if (!empty($lesson)) {
                            if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                                return [
                                    'content_type' => Webinar::$textLesson,
                                    'content' => $lesson,
                                    'link' => route('web.lesson.read', [
                                        "slug" => $this->webinar->slug,
                                        "lesson_id" => $lesson->id
                                    ])
                                ];
                            } else {
                                return $this->getNextContent($lesson->id, Webinar::$textLesson);
                            }
                        } else {
                            // 3. no lesson in the existing chapter, check for quiz in existing chapter
                            $quiz = Quiz::where([
                                'webinar_id' => $this->webinar->id,
                                'chapter_id' => $currentLesson->chapter_id,
                                'text_lesson_id' => null,
                                'status' => Quiz::ACTIVE,
                            ])->first();
                            if (!empty($quiz)) {
                                if ($quiz->attemptable($this->user)) {
                                    return [
                                        'content_type' => Webinar::$quiz,
                                        'content' => $quiz,
                                        'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                    ];
                                } else {
                                    return $this->getNextContent($quiz->id, Webinar::$quiz);
                                }
                            } else {
                                // 4. get first content in next available chapter
                                return $this->getNextChapterFirstContent($currentLesson->chapter_id);
                            }
                        }
                    }
                } elseif (!empty($currentQuiz->chapter_id)) {
                    //Quiz is not associated with any lesson, but is associated with a chapter
                    $quiz = Quiz::where([
                        'webinar_id' => $this->webinar->id,
                        'chapter_id' => $currentQuiz->chapter_id,
                        'text_lesson_id' => null,
                        'status' => Quiz::ACTIVE,
                    ])->where('id', '>', $currentQuiz->id)
                        ->first();
                    if (!empty($quiz)) {
                        if ($quiz->attemptable($this->user)) {
                            return [
                                'content_type' => Webinar::$quiz,
                                'content' => $quiz,
                                'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                            ];
                        } else {
                            return $this->getNextContent($quiz->id, Webinar::$quiz);
                        }
                    } else {
                        // _. get first content in next available chapter
                        return $this->getNextChapterFirstContent($currentQuiz->chapter_id);
                    }
                } else {
                    // Quiz is neither associated with a lesson nor a chapter
                    $quiz = Quiz::where([
                        'webinar_id' => $this->webinar->id,
                        'chapter_id' => null,
                        'text_lesson_id' => null,
                        'status' => Quiz::ACTIVE,
                    ])->where('id', '>', $currentQuiz->id)
                        ->first();
                    if (!empty($quiz)) {
                        if ($quiz->attemptable($this->user)) {
                            return [
                                'content_type' => Webinar::$quiz,
                                'content' => $quiz,
                                'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                            ];
                        } else {
                            return $this->getNextContent($quiz->id, Webinar::$quiz);
                        }
                    } else {
                        /* If no quiz exists outside of a chapter after current quiz,
                        then there is no further content as orphan quizzes are displayed at the end */
                        return null;
                    }
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Method to fetch the previous Course Content (Text Lesson or Quiz/Assessment),
     * based on the current lesson/quiz
     *
     * @param integer $currentContentId
     * @param string $currentContentType
     * @return mixed
     */
    public function getPreviousContent(int $currentContentId, string $currentContentType)
    {
        if (empty($this->webinar)) {
            $this->getWebinarByContentId($currentContentId, $currentContentType);
        }
        if (!empty($this->webinar)) {
            // QUIZ-LOGIC
            if ($currentContentType === Webinar::$quiz) {
                $currentQuiz = Quiz::find($currentContentId);
                if (!empty($currentQuiz)) {
                    // 1. Check if this quiz was neither part of chapter nor lesson
                    if (empty($currentQuiz->chapter_id) && empty($currentQuiz->text_lesson_id)) {
                        // 1.1 check if there is any other 'unassociated' quiz before this quiz
                        $quiz = Quiz::where([
                            'webinar_id' => $this->webinar->id,
                            'text_lesson_id' => null,
                            'chapter_id' => null,
                            'status' => Quiz::ACTIVE,
                        ])->where('id', '<', $currentQuiz->id)->orderByDesc('id')->first();
                        if (!empty($quiz)) {
                            if ($quiz->attemptable($this->user)) {
                                return [
                                    'content_type' => Webinar::$quiz,
                                    'content' => $quiz,
                                    'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                ];
                            } else {
                                return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                            }
                        }
                    } else {
                        // 2. Check if this quiz was part of a chapter, but not lesson
                        if (!empty($currentQuiz->chapter_id) && empty($currentQuiz->text_lesson_id)) {
                            // 2.1 check if there is any other quiz part of the same chapter (but no lesson), before current quiz
                            $quiz = $quiz = Quiz::where([
                                'webinar_id' => $this->webinar->id,
                                'text_lesson_id' => null,
                                'chapter_id' => $currentQuiz->chapter_id,
                                'status' => Quiz::ACTIVE,
                            ])->where('id', '<', $currentQuiz->id)->orderByDesc('id')->first();
                            if (!empty($quiz)) {
                                if ($quiz->attemptable($this->user)) {
                                    return [
                                        'content_type' => Webinar::$quiz,
                                        'content' => $quiz,
                                        'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                    ];
                                } else {
                                    return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                                }
                            } else {
                                // 2.2 check if there is any lesson at the end of the chapter? (since chapter quizzes are shown at the end of chapter)
                                $lesson = TextLesson::where([
                                    'webinar_id' => $this->webinar->id,
                                    'chapter_id' => $currentQuiz->chapter_id,
                                    'status' => TextLesson::$Active,
                                ])->orderByDesc('order')->orderByDesc('id')->first();
                                if (!empty($lesson)) {
                                    if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                                        return [
                                            'content_type' => Webinar::$textLesson,
                                            'content' => $lesson,
                                            'link' => route('web.lesson.read', [
                                                "slug" => $this->webinar->slug,
                                                "lesson_id" => $lesson->id
                                            ])
                                        ];
                                    } else {
                                        return $this->getPreviousContent($lesson->id, Webinar::$textLesson);
                                    }
                                } else {
                                    // 2.3 since there is no lesson before this quiz in the current chapter, then fetch last content from previous chapter
                                    return $this->getPreviousChapterLastContent($currentQuiz->chapter_id);
                                }
                            }
                        } else {
                            // 3. check if this quiz was part of a chapter as well as a lesson
                            if (!empty($currentQuiz->chapter_id) && !empty($currentQuiz->text_lesson_id)) {
                                $currentLesson = TextLesson::find($currentQuiz->text_lesson_id);
                                // 3.1 check if there is any other quiz part of the same lesson, before current quiz
                                $quiz = Quiz::where([
                                    'webinar_id' => $this->webinar->id,
                                    'text_lesson_id' => $currentQuiz->text_lesson_id,
                                    'status' => Quiz::ACTIVE,
                                ])->where('id', '<', $currentQuiz->id)->orderByDesc('id')->first();
                                if (!empty($quiz)) {
                                    if ($quiz->attemptable($this->user)) {
                                        return [
                                            'content_type' => Webinar::$quiz,
                                            'content' => $quiz,
                                            'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                        ];
                                    } else {
                                        return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                                    }
                                } else {
                                    // 3.2 return lesson if it is accessible
                                    if (userHasDripFeedAccess($currentLesson->id, $this->user->id)) {
                                        return [
                                            'content_type' => Webinar::$textLesson,
                                            'content' => $currentLesson,
                                            'link' => route('web.lesson.read', [
                                                "slug" => $this->webinar->slug,
                                                "lesson_id" => $currentLesson->id
                                            ])
                                        ];
                                    } else {
                                        // 3.3 else fetch previous content
                                        return $this->getPreviousContent($currentLesson->id, Webinar::$textLesson);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    return null;
                }
            } else if (in_array($currentContentType, [Webinar::$textLesson, 'text_lesson'])) {
                // LESSON-LOGIC
                $currentLesson = TextLesson::find($currentContentId);
                if (!empty($currentLesson)) {
                    // 1. Check if there is any other lesson before current lesson in current chapter
                    $lesson = TextLesson::where([
                        'webinar_id' => $this->webinar->id,
                        'chapter_id' => $currentLesson->chapter_id,
                        'status' => TextLesson::$Active,
                    ]);
                    if ($currentLesson->order !== 0 && empty($currentLesson->order)) {
                        $lesson = $lesson->where('id', '<', $currentLesson->id);
                    } else {
                        $lesson = $lesson->where('order', '<', $currentLesson->order);
                    }
                    $lesson = $lesson->orderByDesc('order')->orderByDesc('id')->first();
                    if (!empty($lesson)) {
                        // 1.1 check if this lesson has any quiz attached (fetch the last quiz)
                        $quiz = Quiz::where([
                            'webinar_id' => $this->webinar->id,
                            'text_lesson_id' => $lesson->id,
                            'status' => Quiz::ACTIVE,
                        ])->orderByDesc('id')->first();
                        if (!empty($quiz)) {
                            if ($quiz->attemptable($this->user)) {
                                return [
                                    'content_type' => Webinar::$quiz,
                                    'content' => $quiz,
                                    'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                                ];
                            } else {
                                return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                            }
                        } else {
                            if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                                // 1.2 fetch the lesson
                                return [
                                    'content_type' => Webinar::$textLesson,
                                    'content' => $lesson,
                                    'link' => route('web.lesson.read', [
                                        "slug" => $this->webinar->slug,
                                        "lesson_id" => $lesson->id
                                    ])
                                ];
                            } else {
                                return $this->getPreviousContent($lesson->id, Webinar::$textLesson);
                            }
                        }
                    } else {
                        //2. fetch last content from previous chapter
                        return $this->getPreviousChapterLastContent($currentLesson->chapter_id);
                    }
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Method to fetch the very first Content (Text Lesson or Quiz/Assessment) in the next chapter,
     * based on the current chapter
     *
     * @param integer $currentContentId
     * @param string $currentContentType
     * @return mixed
     */
    public function getNextChapterFirstContent(int $currentChapterId)
    {
        $currentChapter = WebinarChapter::find($currentChapterId);
        //get next chapter within the same webinar
        $chapter = WebinarChapter::where([
            'webinar_id' => $currentChapter->webinar_id,
            'status' => WebinarChapter::$chapterActive
        ]);
        $chapter = $chapter->where('id', '!=', $currentChapter->id);
        if ($currentChapter->order !== 0 && !empty($currentChapter->order)) {
            $chapter = $chapter->where('order', '>=', $currentChapter->id);
        } else {
            //chapter doesn't have an order applied, (i.e., $currentChapter->order = null)
            $chapter = $chapter->where('id', '>', $currentChapter->id);
        }
        $chapter = $chapter->orderBy('order')->orderBy('id')->first();

        if (!empty($chapter)) {
            // 1. Check for first attached lesson
            $lesson = TextLesson::where([
                'webinar_id' => $currentChapter->webinar_id,
                'chapter_id' => $chapter->id,
                'status' => TextLesson::$Active
            ])->orderBy('order')->orderBy('id')->first();
            if (!empty($lesson)) {
                //does the user have access to the text lesson?
                if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                    $this->webinar = Webinar::find($lesson->webinar_id);
                    return [
                        'content_type' => Webinar::$textLesson,
                        'content' => $lesson,
                        'link' => route('web.lesson.read', [
                            "slug" => $this->webinar->slug,
                            "lesson_id" => $lesson->id
                        ])
                    ];
                } else {
                    return $this->getNextContent($lesson->id, Webinar::$textLesson);
                }
            } else {
                // 2. Otherwise, check for first attached quiz
                $quiz = Quiz::where([
                    'webinar_id' => $currentChapter->webinar_id,
                    'chapter_id' => $chapter->id,
                    'text_lesson_id' => null,
                    'status' => Quiz::ACTIVE,
                ])->first();
                if (!empty($quiz)) {
                    if ($quiz->attemptable($this->user)) {
                        return [
                            'content_type' => Webinar::$quiz,
                            'content' => $quiz,
                            'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                        ];
                    } else {
                        return $this->getNextContent($quiz->id, Webinar::$quiz);
                    }
                } else {
                    // 4. get first content in next available chapter
                    return $this->getNextChapterFirstContent($chapter->id);
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Method to fetch the very last Content (Text Lesson or Quiz/Assessment) in the next chapter,
     * based on the current chapter
     *
     * @param integer $currentContentId
     * @param string $currentContentType
     * @return mixed
     */
    public function getPreviousChapterLastContent(int $currentChapterId)
    {
        $currentChapter = WebinarChapter::find($currentChapterId);
        // 1. check if previous chapter exists
        //get previous chapter within the same webinar
        $chapter = WebinarChapter::where([
            'webinar_id' => $this->webinar->id,
            'status' => WebinarChapter::$chapterActive
        ]);
        $chapter = $chapter->where('id', '!=', $currentChapter->id);
        if ($currentChapter->order !== 0 && !empty($currentChapter->order)) {
            $chapter = $chapter->where('order', '>=', $currentChapter->id);
        } else {
            //chapter doesn't have an order applied, (i.e., $currentChapter->order = null)
            $chapter = $chapter->where('id', '<', $currentChapter->id);
        }
        $chapter = $chapter->orderByDesc('order')->orderByDesc('id')->first();
        if (!empty($chapter)) {
            // 1.1 check for quiz not attached to any lesson in chapter, get last quiz
            $quiz = Quiz::where([
                'webinar_id' => $this->webinar->id,
                'text_lesson_id' => null,
                'chapter_id' => $chapter,
                'status' => Quiz::ACTIVE,
            ])->orderByDesc('id')->first();
            if (!empty($quiz)) {
                if ($quiz->attemptable($this->user)) {
                    return [
                        'content_type' => Webinar::$quiz,
                        'content' => $quiz,
                        'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                    ];
                } else {
                    return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                }
            } else {
                // 1.2 check for last lesson in chapter
                $lesson = TextLesson::where([
                    'webinar_id' => $this->webinar->id,
                    'chapter_id' => $chapter->id,
                    'status' => TextLesson::$Active,
                ]);
                $lesson = $lesson->orderByDesc('order')->orderByDesc('id')->first();
                if (!empty($lesson)) {
                    // 1.2.1 check if quiz is associated with lesson, fetch last one
                    $quiz = Quiz::where([
                        'webinar_id' => $this->webinar->id,
                        'text_lesson_id' => $lesson->id,
                        'status' => Quiz::ACTIVE,
                    ])->orderByDesc('id')->first();
                    if (!empty($quiz)) {
                        if ($quiz->attemptable($this->user)) {
                            return [
                                'content_type' => Webinar::$quiz,
                                'content' => $quiz,
                                'link' => route('panel.quizzes.start', ["id" => $quiz->id])
                            ];
                        } else {
                            return $this->getPreviousContent($quiz->id, Webinar::$quiz);
                        }
                    } else {
                        // 1.2.2 return lesson if it is accessible
                        if (userHasDripFeedAccess($lesson->id, $this->user->id)) {
                            return [
                                'content_type' => Webinar::$textLesson,
                                'content' => $lesson,
                                'link' => route('web.lesson.read', [
                                    "slug" => $this->webinar->slug,
                                    "lesson_id" => $lesson->id
                                ])
                            ];
                        } else {
                            // 1.2.3 else return previous content
                            return $this->getPreviousContent($lesson->id, Webinar::$textLesson);
                        }
                    }
                } else {
                    // 1.3 since nothing was found in this chapter, call getPreviousChapterLastContent again
                    return $this->getPreviousChapterLastContent($lesson->chapter_id);
                }
            }
        } else {
            // 2. null
            return null;
        }
    }


    /**
     * Method to fetch a list of webinars available to organizaiton personnel
     * based on the Organization ID
     *
     * @param integer $organId
     * @return mixed
     */
    public function getWebinarsVisibleToOrganization(int $organId = null)
    {
        $organId = $organId ?? $this->user->organ_id;
        $visibleWebinars = null;
        if (is_null($organId) && $this->user->isOrganizationPersonnel()) {
            $organId = $this->user->isOrganization() ? $this->user->id : $this->user->organ_id;
        }

        if (empty($organId) && $this->user->isTeacher()) {
            $visibleWebinars = Webinar::whereIn('type', ['course', 'text_lesson'])
                ->where(['status' => 'active'])->get();
        } else if (!empty($organId)) {
            $webinarsIdsVisibleToOrganization = CourseVisibility::where('organization_id', $organId)
                ->pluck('course_id')->toArray();
            $visibleWebinars = Webinar::whereIn('type', ['course', 'text_lesson'])
                ->where(['status' => 'active', 'visible_to_all' => 1])
                // ->orWhere(['status' => 'active', 'visible_to_all' => 1])
                ->orWhere(function ($q) use ($webinarsIdsVisibleToOrganization) {
                    $q->where('status', 'active');
                    $q->whereIn('id', $webinarsIdsVisibleToOrganization);
                })->get();
        }
        return $visibleWebinars;
    }

    /**
     * This method is defined to send the notification to the organization on the student's course competion
     * @return void
     */
    public function courseCompletionNotification($webinar)
    {
        $studentCourseProgress = $webinar->getProgress();
        $userOrganization = $this->user->organization;

        if ($studentCourseProgress == 100) {

            $webinarNotification = WebinarNotification::where([
                "user_id" => $this->user->id,
                "webinar_id" => $webinar->id,
            ])->first();

            if (!$webinarNotification) {

                WebinarNotification::create([
                    "user_id" => $this->user->id,
                    "webinar_id" => $webinar->id,
                    "is_completed" => 1
                ]);

                $mailContent = [
                    "organizationName" => $userOrganization->full_name,
                    "studentName" => $this->user->full_name,
                    "courseName" => $webinar->title,
                    "studentId" => $this->user->id,
                ];

                \Mail::to($userOrganization->email)->send(new \App\Mail\CourseCompletionMail($mailContent));
            }
        }
    }


    //function to get the sorted chapterItems as same as displayed in sideBar of course view page, this function will return the array of items
    public static function getChapterItems($webinar)
    {
        $chapterItems = [];
        //checking the custom setting table to get the value of dripFeedShow
        // $dripFeedAllow = CustomSetting::where('key', config('course_settings.showDripFeedInSideBar'))->first();
        // following variable will store the array of quizzes that are associated with a chapter
        $textLessonQuizzess = null;
        //all chapter Items
        foreach ($webinar->chapters as $index => $chapter) {
            if ($chapter->status == 'active') {
                foreach ($chapter->chapterItems as $item) {
                    if ($item->type == 'text_lesson' && $item->textLesson->status == 'active') {

                        $chapterItems[]  = $item;

                        if (!empty($item->textLesson->quizzes) && count($item->textLesson->quizzes) > 0) {
                            $textLessonQuizzess =
                                array_filter($item->textLesson->quizzes->toArray(), function ($quiz) {
                                    return $quiz['status'] === 'active';
                                });

                            if (!empty($textLessonQuizzess)) {
                                foreach ($textLessonQuizzess as $lessonQuiz) {
                                    if ($lessonQuiz !== null && $lessonQuiz['chapter_id'] == $item->chapter_id) {
                                        $chapterItems[] = WebinarChapterItem::where('item_id', $lessonQuiz['id'])->where('type', 'quiz')->first();
                                    }
                                }
                            }
                        }
                    } elseif ($item->type == 'file' && $item->file->status == 'active') {
                        $chapterItems[]  = $item;
                    }
                }
                //For Quizzes which are not asssociated with text_lesson
                $allQuizzess = Quiz::where('chapter_id', $chapter->id)->where('text_lesson_id', NULL)->where('status', 'active')->get();
                if (!blank($allQuizzess) && count($allQuizzess) > 0) {
                    foreach ($allQuizzess as $chapQuiz) {

                        $chapterItems[] = WebinarChapterItem::where('item_id', $chapQuiz->id)->where('type', 'quiz')->first();
                    }
                }
            }
        }
        return $chapterItems;
    }

    // function to get the previous item of the chapter
    public static function getPreviousItem($currentItem, array $chapItems, $type)
    {
        $previous = null;

        $currentIndex = null;
        //currentItem
        $currentChapItem = WebinarChapterItem::where('item_id', $currentItem->id)->where('type', $type)->first();

        if (!blank($chapItems) && count($chapItems) > 0) {

            // check the current index
            foreach ($chapItems as $key => $item) {

                if ($item->id == $currentChapItem->id) {
                    $currentIndex = $key; // Update the index when found
                    break; // Exit the loop once found
                }
            }
            if ($currentIndex > 0) {

                $previous = $chapItems[$currentIndex - 1];
            }
        }
        return $previous;
    }

    // function to get the next item of the chapter
    public static function getNextItem($currentItem, array $chapItems, $type)
    {
        // nessecaary varibales
        $next = null;
        $currentIndex = null;
        //currentItem
        $currentChapItem = WebinarChapterItem::where('item_id', $currentItem->id)->where('type', $type)->first();
        // check the current index
        if (!blank($chapItems) && count($chapItems) > 0) {
            foreach ($chapItems as $key => $item) {

                if ($item->id == $currentChapItem->id) {
                    $currentIndex = $key; // Update the index when found
                    break; // Exit the loop once found
                }
            }

            if ($currentIndex < count($chapItems) - 1) {
                $next = $chapItems[$currentIndex + 1];
            }
        }
        return $next;
    }

    /**
     * Check if the student has learnt the course content.
     *
     * @param string $itemType Course content type: text_lesson, file, or session.
     * @param int $itemId Course content ID: text_lesson_id, file_id, or session_id.
     *
     * @return bool Returns true if the student has learnt the course content; otherwise, false.
    */
    public static function isLearnt($itemType, $itemId, $student = null)
    {
        $user = $student ? $student : Auth::user();
        $contentId = ($itemType === 'text_lesson') ? 'text_lesson_id' : (($itemType === 'file') ? 'file_id' : (($itemType === 'session') ? 'session_id' : ''));

        if ($user && $user->isUser() && $contentId) {

            $isLearnt = CourseLearning::where([
                'user_id' => $user->id, // Assuming user ID is stored in 'id' column
                $contentId => $itemId
            ])->first();
            return $isLearnt ? true : false; // You might want to return a boolean value based on the result
        }

        return false; // Default return value if conditions are not met
    }

    // function to calculate the user's Quiz result
    public static function studentQuizResult($quiz_id, $student_id)
    {

        $quizResult = QuizzesResult::where([
            'quiz_id' => $quiz_id,
            'user_id' => $student_id
        ])->first();


        return $quizResult;
    }

    /**
     * Responsibility: Calculate the statistics of completed and visited items of a course with respect to a student.
     * @param $course (object): Purchase course data from the sale tables. Can be overridden by initializing and utilizing the argument $webinar (which will represent the actual course).
     * @param $student (object): User object representing a student.
     * @return array: An array containing total chapters, chapters in learning, total text lessons, files, and quizzes, as well as the completed text lessons, files, and passed quizzes.
     */
    public static function courseStats ($coursePurchased, $student) {

        $chapters = count($coursePurchased->webinar->chapters);
        $textLessons = count($coursePurchased->webinar->textLessons);
        $files = count($coursePurchased->webinar->files);
        $quizzes = count($coursePurchased->webinar->quizzes);
        $chaptersInProgress = 0;
        $lessonsLearnt = 0;
        $filesVisited = 0;
        $passedQuizzes = 0;

        // calculating the statics of quizzes which are not part of any chapter
        if(count($coursePurchased->webinar->quizzes->whereNull('chapter_id')) > 0) {
            $quizzesWithoutChapter = $coursePurchased->webinar->quizzes->whereNull('chapter_id');
            foreach ($quizzesWithoutChapter as $quizWithNoChapter) {

                $quizResult = QuizzesResult::where([
                    'quiz_id' => $quizWithNoChapter->id,
                    'user_id' => $student->id,
                    'status' => 'passed',
                ])->first();

                $passedQuizzes = $quizResult ? $passedQuizzes + 1 : $passedQuizzes;
            }

        }

        // calculating the statics of chapter items
        foreach ($coursePurchased->webinar->chapters as $chapter) {
            // calling the chapterProgress method from this helper class
            $chapterProgress = self::chapterProgress($chapter, $student);
            $chaptersInProgress = ($chapterProgress['visitedItems'] > 0) ? $chaptersInProgress + 1 : $chaptersInProgress;
            $lessonsLearnt = ($chapterProgress['lessonsLearnt'] > 0) ? $lessonsLearnt + 1 : $lessonsLearnt;
            $filesVisited = ($chapterProgress['filesVisited'] > 0) ? $filesVisited + 1 : $filesVisited;
            $passedQuizzes = ($chapterProgress['passedQuizzes'] > 0) ? $passedQuizzes + 1 : $passedQuizzes;
        }

        return [
            'totalChapters' => $chapters,
            'totalLesons' => $textLessons,
            'totalFiles' => $files,
            'totalQuizzes' => $quizzes,
            'chaptersInProgress' => $chaptersInProgress,
            'lessonsLearnt' => $lessonsLearnt,
            'filesVisited' => $filesVisited,
            'passedQuizzes' => $passedQuizzes
        ];
    }
    /**
     * Responsibility: Calculate the statistics of completed and visited items of a chapter with respect to a student.
     * @param $chapter (object)
     * @param $student (object): User object representing a student.
     * @return array: An array containing total chapterItems,as well as the completed text lessons, files, and passed quizzes.
     */
    public static function chapterProgress ($chapter, $student)
    {
        $chapter_items = $chapter->chapterItems;
        $chapterItemsCount = count($chapter_items);
        $quizCount = 0;
        $passedQuizzes = 0;
        $visitedItems = 0;
        $lessonsLearnt = 0;
        $filesVisited = 0;

        foreach($chapter_items as $item) {

            if ($item->type == 'quiz' && $item->quiz->status == 'active') {
                $quizCount ++;

                $quizResult = QuizzesResult::where([
                    'quiz_id' => $item->quiz->id,
                    'user_id' => $student->id,
                    'status' => 'passed',
                ])->first();

                $passedQuizzes = $quizResult ? $passedQuizzes + 1 : $passedQuizzes;

            } else {

                $contentType = ($item->type === 'text_lesson') ? 'text_lesson_id' : (($item->type === 'file') ? 'file_id' : (($item->type === 'session') ? 'session_id' : ''));
                $itemType = ($item->type === 'text_lesson') ? 'textLesson' : (($item->type === 'file') ? 'file' : (($item->type === 'session') ? 'session' : ''));
                if ($item->$itemType && $item->$itemType->id) {

                    $isLearnt = CourseLearning::where([
                        'user_id' => $student->id, // Assuming user ID is stored in 'id' column
                        $contentType => $item->$itemType->id
                    ])->first();
                }
                $lessonsLearnt = ($isLearnt && $item->type === 'text_lesson') ? $lessonsLearnt + 1 : $lessonsLearnt;
                $filesVisited = ($isLearnt && $item->type === 'file') ? $filesVisited + 1 : $filesVisited;
                $visitedItems = $isLearnt ? $visitedItems + 1 : $visitedItems;
            }
        }

        $visitedItems = $visitedItems + $passedQuizzes;
        $progress = ($chapterItemsCount > 0 ) ? round((($visitedItems / $chapterItemsCount )*100),2): 0;
        return [
            'chapterItemsCount' => $chapterItemsCount,
            'passedQuizzes' => $passedQuizzes,
            'lessonsLearnt' => $lessonsLearnt,
            'filesVisited' => $filesVisited,
            'visitedItems' => $visitedItems,
            'progress'  => $progress,
        ];
    }
}
