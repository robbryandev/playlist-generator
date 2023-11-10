<?php

namespace App\Utils;

class SpotifyToken
{
  public string $token;
  public int $expires;

  public function __construct(string $token, int $expires) {
    $this->token = $token;
    $this->expires = $expires;
  }

  public function getToken() {
    return $this->token;
  }

  public function refresh() {
    // To-do
  }

  public function expired() {
    return $this->expires <= 0;
  }
}