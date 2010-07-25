# Customization

## Getting sidebar content

An internal controller is provided to allow developers to insert

- Recent posts
- Popular posts (of the week)
- Recent comments

into their blog application.  These methods are accessed with

    $recent_posts = Request::factory( Route::get('blog/stats')
        ->uri(array('action' => 'recent_articles', 'limit' => 10)) )
        ->execute()->response;

    $popular_posts = Request::factory( Route::get('blog/stats')
        ->uri(array('action' => 'recent_articles', 'limit' => 5)) )
        ->execute()->response;

    $recent_comments = Request::factory( Route::get('blog/stats')
        ->uri(array('action' => 'recent_comments', 'limit' => 3)) )
        ->execute()->response;
