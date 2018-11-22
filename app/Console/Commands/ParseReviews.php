<?php

namespace App\Console\Commands;

use App\Review;
use App\Teacher;
use Illuminate\Console\Command;
use App\ReviewDetectors;
use \StanfordTagger\CRFClassifier;

class ParseReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse reviews';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Teacher::orderBy('updated_at')->chunk(100, function ($teachers) {
            foreach ($teachers as $teacher) {
                $this->parseTeacherReviews($teacher->id);
                $teacher->update();
            }
        });
    }
    private function parseTeacherReviews($teacher_id)
    {

        $i = 1;
        while (true) {
            $this->info('Parse teachers reviews ID:' . $teacher_id . '. Page: ' . $i);
            $data = $this->getPageData($i, $teacher_id);
            $this->parseData($data);
            if(isset($data->meta->has_next) == false || (bool) $data->meta->has_next == false) {
                break;
            }
            $i++;
            sleep(30);
        }
    }
    private function getPageData($page = 1, $teacher_id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://www.italki.com/api/teacher/comment', [
            'query' => [
                'lesson_language' => 'english',
                'page' => $page,
                'page_size' => 20,
                'teacher_id' => $teacher_id
            ]
        ]);
        return json_decode($response->getBody());
    }
    private function parseData($data)
    {

        if(isset($data->data->comments) && is_array($data->data->comments)) {
            foreach ($data->data->comments as $review) {
                $detector = new ReviewDetectors($review->student_comment, new CRFClassifier());
                if(
                    isset($review->student_comment)
                    && is_string($review->student_comment)
                    && mb_strlen($review->student_comment) > 10
                    && mb_strlen($review->student_comment) < 190
                    && $detector->isAble()
                ) {
                    $this->comment($review->student_comment);
                    Review::withTrashed()->firstOrCreate(['text' => $review->student_comment]);
                }
            }
        }
    }
}
