<?php

namespace App\Classes;

class SpotifyTrack {
  public string $id;
  public string $name;
  public string $artistName;
  public string $preview;
  public string $img;
  function __construct(string $id, string $name, string $artistName, string $preview, string $img) {
      $this->id = $id;
      $this->name = $name;
      $this->artistName = $artistName;
      $this->preview = $preview;
      $this->img = $img;
  }
}