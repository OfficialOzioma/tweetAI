<?php

namespace App\Jobs;

use Exception;
use App\Models\Post;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Comment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Exception\RequestException;
use App\Events\AutobotCreated;

class GenerateAutobots implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $maxRetries = 5; // Maximum number of retries
    protected $retryDelay = 1000; // Initial delay in milliseconds

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $autobots = [];
            $posts = [];
            $comments = [];
            $now = Carbon::now(); // Capture current time to use for timestamps

            // Create 500 Autobots
            for ($i = 0; $i < 500; $i++) {
                $userData = $this->getAutobotData();

                // Prepare the Autobot data for batch insert
                $autobots[] = [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $userId = $i + 1; // Simulate user IDs in the batch process (Laravel will assign real IDs upon insertion)

                // Create 10 posts per Autobot
                for ($j = 0; $j < 10; $j++) {
                    $postData = $this->getPostData();

                    // Prepare the Post data for batch insert
                    $posts[] = [
                        'user_id' => $userId,
                        'title' => $postData['title'],
                        'body' => $postData['body'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $postId = count($posts); // Simulate post IDs in the batch process

                    // Create 10 comments per post
                    for ($k = 0; $k < 10; $k++) {
                        $commentData = $this->getCommentData();

                        // Prepare the Comment data for batch insert
                        $comments[] = [
                            'post_id' => $postId,
                            'name' => $commentData['name'],
                            'email' => $commentData['email'],
                            'body' => $commentData['body'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            DB::transaction(function () use ($autobots, $posts, $comments) {
                // Batch insert Autobots
                if (!empty($autobots)) {
                    User::insert($autobots); // Insert all users at once

                    $count = User::count();
                    event(new AutobotCreated($count));
                }

                // Batch insert Posts
                if (!empty($posts)) {
                    Post::insert($posts); // Insert all posts at once
                }

                // Batch insert Comments
                if (!empty($comments)) {
                    Comment::insert($comments); // Insert all comments at once
                }
            });
        } catch (Exception $e) {
            // Log any errors
            Log::error('Error while generating Autobots: ' . $e->getMessage());
            throw $e; // Optionally rethrow to retry the job
        }
    }

    protected function getAutobotData()
    {
        return $this->requestWithRetry('https://jsonplaceholder.typicode.com/users');
    }

    protected function getPostData()
    {
        return $this->requestWithRetry('https://jsonplaceholder.typicode.com/posts');
    }

    protected function getCommentData()
    {
        return $this->requestWithRetry('https://jsonplaceholder.typicode.com/comments');
    }

    protected function requestWithRetry($url)
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->client->get($url);
                return json_decode($response->getBody(), true);
            } catch (RequestException $e) {
                // Check for rate limiting (usually 429 Too Many Requests)
                if ($e->getCode() === 429) {
                    Log::warning('Rate limit exceeded for URL: ' . $url);
                } else {
                    Log::error('Request failed for URL: ' . $url . ' with error: ' . $e->getMessage());
                }

                // Exponential backoff
                $attempt++;
                $delay = $this->retryDelay * pow(2, $attempt - 1); // Exponential backoff
                Log::info('Retrying request for URL: ' . $url . ' in ' . $delay . ' milliseconds');
                usleep($delay * 1000); // Convert milliseconds to microseconds
            }
        }

        // If we exhausted retries, throw an exception
        throw new Exception('Failed to fetch data from URL after ' . $this->maxRetries . ' attempts');
    }
}
