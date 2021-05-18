<?php

namespace App\Helper;

use Abraham\TwitterOAuth\TwitterOAuth;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TwitterHelper
{

  private $connection;
  private bool $twitterEnable;

  function __construct(ParameterBagInterface $params)
  {
    $consumerKey = $params->get('consumer_key');
    $consumerSecret = $params->get('consumer_secret');
    $oauthToken = $params->get('oauth_token');
    $oauthTokenSecret = $params->get('oauth_token_secret');

    $this->setTwitterEnabled(filter_var($params->get('twitter_enable'), FILTER_VALIDATE_BOOLEAN));

    $this->connection = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
  }

  public function getConnection()
  {
    return $this->connection;
  }

  public function getUserIdByScreenName(string $screen_name)
  {
    $result = $this->connection->get("users/lookup", ['screen_name' => $screen_name], true);

    if (isset($result->errors)) {
      return null;
    }

    return $result[0]->id;
  }

  public function sendDirectMessage($user_id, $str = 'BLANK')
  {
    $data = [
      'event' => [
        'type' => 'message_create',
        'message_create' => [
          'target' => [
            'recipient_id' => $user_id
          ],
          'message_data' => [
            'text' => $str
          ]
        ]
      ]
    ];

    $result =  $this->connection->post('direct_messages/events/new', $data, true);

    return $result;
  }

  public function sendDirectMessageWithScreenName($screen_name, $str = 'BLANK')
  {
    $user_id = $this->getUserIdByScreenName($screen_name);

    return $this->sendDirectMessage($user_id, $str);
  }

  public function sendTweet($str = '', $mediaArray = null)
  {


    if (is_array($mediaArray)) {

      $mediaIDS = array();

      foreach ($mediaArray as $key => $media_path) {
        if (!is_readable($media_path) || file_get_contents($media_path) === false) {
          // TODO logger
        } else {
          $mediaOBJ = $this->connection->upload('media/upload', ['media' => $media_path]);
          array_push($mediaIDS, $mediaOBJ->media_id_string);
        }
      }

      $mediaIDstr = implode(',', $mediaIDS);

      $arrayCfg['media_ids'] = $mediaIDstr;
    }

    $arrayCfg['status'] = $str;

    $result = $this->connection->post("statuses/update", $arrayCfg);

    return $result;
  }

  public function getTwitterEnable() {
    return $this->twitterEnable;
  }

  private function setTwitterEnabled(bool $bool)
  {
    $this->twitterEnable = $bool;
  }
}
