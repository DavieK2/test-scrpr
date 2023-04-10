<?php

    use Symfony\Component\HttpClient\HttpClient;

    require __DIR__ .'/vendor/autoload.php';

    $link = $_POST['url'] ?? NULL;

    function checkIfPageIsProperlyTranslate($browser, $link){

        //Get links to other pages
        try {


            $b = $browser->request('GET', $link);

            $image = $b->filter('#learning-illus > img')->attr('src');

            if(filter_var($image, FILTER_VALIDATE_URL)){
                
                $img = get_headers($image, 1);

               
                if($img["Content-Length"]/1024 < 20){
                    return [$link => [
                        'errors' => ['Image resolution is not good']
                    ]];
                }
            }

            $title_text = $b->filter('title')->text();

            $browser->request("GET", 'https://api.dandelion.eu/datatxt/li/v1?token=0ba45bb4edfe4a51951288128b98f429&text='.$title_text);

            if($browser->getResponse()->toArray()['detectedLangs'][0]['lang'] != 'hi'){
                return [$link => [
                    'errors' => ['One or more pages are not translated properly']
                ]];
            }

            $links = $b->filter('a')->each(function($node, $i) {
                return $node->link()->getUri(); 
              });
  
              $totalLinks = (count($links));
  
              $randomLinks = [];
  
              for ($i=0; $i < 5; $i++) { 
                  $randomLinks[] = $links[rand(0, $totalLinks)];
              }

              foreach($randomLinks as $randomLink){
                    $b = $browser->request('GET', $randomLink);
                    $title_text = $b->filter('title')->text();
                    $browser->request("GET", 'https://api.dandelion.eu/datatxt/li/v1?token=0ba45bb4edfe4a51951288128b98f429&text='.$title_text);

                    if($browser->getResponse()->toArray()['detectedLangs'][0]['lang'] != 'hi'){
                        return [$link => [
                            'errors' => ['One or more pages are not translated properly']
                        ]];
                    }

                    break;
              }

              return [$link => [
                'status' => 'Passed'
            ]];


        } catch (\Throwable $th) {
                return [
                    $link => [
                        'errors' => ['Website is not working properly']
                    ]
                ];
        }
    }

    if($link){


        $browser = new \Symfony\Component\BrowserKit\HttpBrowser(HttpClient::create());

        $form_links = preg_split('/\s{1,}/',$link);

        $form_links = array_filter($form_links, fn($item) => $item != '');

        if(count($form_links) < 1) {

            echo 'Invalid link';

            die();
        }
    
        $translation_status = [];

        foreach ($form_links as $key => $link) {
           
            $translation_status[] = checkIfPageIsProperlyTranslate($browser, $link);
            
        }

       foreach($translation_status as $key =>  $statuses){

            echo '<pre>';
            
            foreach($statuses as $key => $status){
                if(isset($status['errors'])){

                    echo '<h3>'.$key. ': Fail: ('.implode(', ', $status['errors']).')</h3>';
                }
                else{
                    echo '<h3>'.$key. ': ('.implode(', ', $status).')</h3>';
                }
            }

            echo '</pre>';
        }

        die();
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
        <textarea name="url" id="" cols="30" rows="10"></textarea>
        <button>Scrape</button>
    </form>
</body>
</html>