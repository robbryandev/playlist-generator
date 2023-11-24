<?php

namespace App\Classes;

class SpotifyArtist {
  public string $id;
  public string $name;
  public string $img;
  function __construct(string $id, string $name, string $img) {
      $this->id = $id;
      $this->name = $name;
      $this->img = $img;
  }
}