<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Review;
use \StanfordTagger\CRFClassifier;
use App\ReviewDetectors;
class ReviewsChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:checker {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all reviews for english for names etc';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $detector = new ReviewDetectors(new CRFClassifier());
        $limit = intval($this->option('limit')) > 0 ? (integer) $this->option('limit') : 300;

         Review::orderByDesc('checked_at')->take($limit)->chunk(100, function ($reviews) use (&$detector) {
             foreach ($reviews as $review) {
                 if($detector->isNotEnglish($review->text)) {
                     $this->deleteReview($review, 'is not English');
                 } else {
                     if($detector->isPersonal($review->text))
                         $this->deleteReview($review, 'is personal review');
                 }
                 $review->checked();
             }
        });


    }
    private function deleteReview(Review $review, $reason)
    {
        $this->warn('Will delete because ' . $reason . ' ==> ' . $review->text);
        $review->delete();
    }
}
