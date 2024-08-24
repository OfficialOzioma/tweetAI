<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Faker\Factory as Faker;
use App\Events\AutobotCreated;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class GenerateAutobots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autobots:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    protected $client;
    protected $maxRetries = 5; // Maximum number of retries
    protected $retryDelay = 1000; // Initial delay in milliseconds

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron Job running at " . now());

        try {
            $faker = Faker::create();
            $now = Carbon::now();

            $postData = collect($this->getPostData())->shuffle();
            $commentData = collect($this->getCommentData());

            $autobots = [];
            $posts = [];
            $comments = [];

            // Prepare Autobots
            for ($i = 0; $i < 500; $i++) {
                $autobots[] = [
                    'name' => $faker->unique()->name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => bcrypt('password'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::transaction(
                function () use (&$autobots, &$posts, &$comments, $postData, $commentData, $now) {
                    // Insert Autobots and get their IDs
                    User::insert($autobots);
                    $autobotIds = User::pluck('id')->toArray(); // Retrieve IDs

                    // Insert Posts and collect their IDs

                    foreach ($autobotIds as $index => $autobotId) {
                        $autobotPosts = $postData->slice(($index * 10) % 100, 10);
                        foreach ($autobotPosts as  $post) {
                            $posts[] = [
                                'user_id' => $autobotId,
                                'title' => $post['title'],
                                'body' => $post['body'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    // Insert Posts and retrieve inserted IDs
                    $insertedPostIds = [];
                    foreach (array_chunk($posts, 1000) as $postChunk) {
                        Post::insert($postChunk);
                        $insertedPostIds = array_merge($insertedPostIds, Post::pluck('id')->toArray());
                    }

                    // Prepare Comments
                    foreach ($posts as $index => $post) {
                        $postId = $insertedPostIds[$index % count($insertedPostIds)];
                        foreach ($commentData->random(10) as $comment) {
                            $comments[] = [
                                'post_id' => $postId,
                                'name' => $comment['name'],
                                'email' => $comment['email'],
                                'body' => $comment['body'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    // Insert Comments in chunks
                    foreach (array_chunk($comments, 1000) as $commentChunk) {
                        Comment::insert($commentChunk);
                    }

                    $count = User::count();
                    event(new AutobotCreated($count));
                    Log::info('AutobotCreated event dispatched with count: ' . $count);
                }

            );
        } catch (Exception $e) {
            Log::error('Error while generating Autobots: ' . $e->getMessage());
            throw $e;
        }

        info("Cron Job finished at " . now());
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
                // $response = $this->client->get($url);
                $response = Http::get($url);
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
