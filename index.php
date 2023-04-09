<?php

    use Symfony\Component\HttpClient\HttpClient;

    require __DIR__ .'/vendor/autoload.php';

    $browser = new \Symfony\Component\BrowserKit\HttpBrowser(HttpClient::create());


    $link = $_POST['url'] ?? NULL;

    if($link){

        $file_name = 'books.csv';
        $book = fopen($file_name, 'a');

        while($link){

            $b = $browser->request('GET', $link);
    
            $b->filter('h3')->each(function($node) use($book){
                $title = $node->filter('a')->attr('title');
                fputcsv($book, [$title]);
            } );
    
            try {
                $link = $b->filter('.next > a')->link()->getUri() ?? null;
            } catch (\Throwable $th) {
                $link = null;
            }
        }

        $mime_type = mime_content_type($file_name);
        $file_size = filesize($file_name);

        header("Content-Type: $mime_type");
        header("Content-Length: $file_size");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Expires: 0");
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($file_name);
        
        fclose($book);

        exit();
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Enter Website Name</h1>
    <br>
    <form method="post">
        <input name="url" type="text">
        <button>Scrape</button>
    </form>
</body>
</html>