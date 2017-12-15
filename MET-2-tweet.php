<?php
  // php -f MET-2-tweet.php 202614
  // header("Content-type: text/plain");
  echo phpversion() . "\n";
  chdir( dirname(__FILE__) );
  echo dirname( __FILE__ ). "\n";

  // Test config for open https with PHP
  $w = stream_get_wrappers();
  echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
  echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
  echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
  echo 'wrappers: ', var_export($w);
  echo "\n---\n";
  die;

  // $_sdr = $_SERVER['DOCUMENT_ROOT'];
  require '_classes/twitteroauth/twitter.class.php';
  require '_classes/debuglib.php';
  require 'twiter_keys.php';

  // Get Collection ID argument
  if ( !isset( $argv[1] ) ) {
    echo 'Missing param: Collection ID'; die;
  }
  $collection_id = $argv[1];
  // $collection_id = 264688;

  $data = [];
  // $data['url'] = 'test.html';
  $data['url'] = 'https://www.metmuseum.org/art/collection/search/' . $collection_id; //
  echo $data['url'] . PHP_EOL;
  $data['html'] = file_get_contents( $data['url'] );

  /* Use internal libxml errors -- turn on in production, off for debugging */
  libxml_use_internal_errors(true);
  $dom = new DomDocument;
  $dom->preserveWhiteSpace = false;
  $dom->loadHTML( $data['html'] );

  $xpath = new DomXPath($dom);

  $nodes = $xpath->query('//a[@name="#collectionImage"]/img');
  $data['img_url'] = $nodes->item(0)->getAttribute('ng-src');

  // Sometimes URL has other character data / sorrounding it
  // Think duw to use of Angular, we keep the URl only
  preg_match('#https?://[^/\s]+/\S+\.(jpg|jpeg|png|gif)#i', $data['img_url'], $matches);
  if ( $matches ) {
    $data['img_url'] = $matches[0];
    $data['img_file'] = basename( $data['img_url'] );
    if ( !file_exists( 'img/' . $data['img_file'] ) ) {
      // Doenload file
      file_put_contents( 'img/' . $data['img_file'], file_get_contents( $data['img_url'] ) );
    }
    echo dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $data['img_file'] . "\n";
  } else {
    echo "\n----\nImage not found (?)"; die;
    var_dump( $matches);
    die;
  }

  $nodes = $xpath->query('//*[@class="collection-details__object-title"]');
  $data['title'] = '«' . $nodes->item(0)->nodeValue . '»';
  // echo $data['title'] . '<br>';

  $data['author'] = ' by Anonymous';
  $data['date'] = '';
  $nodes = $xpath->query('//dl[@class="collection-details__tombstone--row"]');
  foreach( $nodes as $n ) {
    $label = $xpath->query('dt[@class="collection-details__tombstone--label"]', $n);
    $value = $xpath->query('dd[@class="collection-details__tombstone--value"]', $n);
    // Author
    if ( in_array( trim( $label->item(0)->nodeValue ), ['Artist:', 'Artists:', 'Designer:'] ) ) {
      $data['author'] = ' by ' . trim( $value->item(0)->nodeValue );
    }
    // Date
    if ( trim( $label->item(0)->nodeValue ) == 'Date:' ) {
      $data['date'] = ' (' . trim( $value->item(0)->nodeValue . ')' );
    }
  }

  echo "\n";
  var_dump ( $data );
  // print_a( $data );

  $tweet = $data['title'] . $data['date'] . $data['author'] . '. ' . $data['url'];
  echo "\n---\n" . $tweet . "\n---\n";

  // Prepare tweet
  if ( isset( $argv[2] ) && $argv[2] == '--send' ) {

    $twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    try {
      // I ha a LOT of problems sending the image
      // Seems that hgas to be relative to the script to work
      // frow commandline
      $tweet =$twitter->send( $tweet, './img/' . $data['img_file'] );
      // unlink( './img/' . $data['img_file'] );
    } catch (TwitterException $e) {
      echo 'Error: ' . $e->getMessage();
      die;
    }
  } else {
    echo "Tweet not sent in absence of --send param.\nphp -f MET-2-tweet.php XXXXX --send";
    die;
  }

  // ----------------------------------

?>