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
        Review::select('id', 'text')->orderByDesc('created_at')->chunk(100, function ($reviews) {
            foreach ($reviews as $review) {
                $detector = new ReviewDetectors($review->text, new CRFClassifier());
                if($detector->isNotEnglish()) $this->deleteReview($review, 'is not English');
                if($detector->isPersonal()) $this->deleteReview($review, 'is personal review');
            }
        });
    }
    private function deleteReview(Review $review, $reason)
    {
        $this->warn('Will delete because ' . $reason . ' ==> ' . $review->text);
        $review->delete();
    }
}
