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
    protected $signature = 'reviews:checker';

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
        $reviews = Review::orderByDesc('checked_at')->take(300)->get();
        foreach ($reviews as $review) {
            if($detector->isNotEnglish($review->text)) {
                $this->deleteReview($review, 'is not English');
            } else {
                if($detector->isPersonal($review->text))
                    $this->deleteReview($review, 'is personal review');
            }
            $review->checked();
        }

    }
    private function deleteReview(Review $review, $reason)
    {
        $this->warn('Will delete because ' . $reason . ' ==> ' . $review->text);
        $review->delete();
    }
}
