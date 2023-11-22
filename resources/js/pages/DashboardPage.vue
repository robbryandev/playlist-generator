<template>
  <div id="artist-picked" class="max-w-sm md:min-w-min md:max-w-md space-y-2">
    <div class="flex flex-row h-24 bg-neutral-700 p-2 rounded-md" v-for="artist in this.selectedArtists" :key="artist.id">
      <img class="rounded-full aspect-square" v-bind:src="artist.img" v-bind:alt="'Picture of ' + artist.name">
      <button class="bg-red-500 rounded-full w-6 h-6 font-semibold self-center ml-4" v-on:click="() => {
        this.selectedArtists = this.selectedArtists.filter((fArtist) => {
          return fArtist.id != artist.id;
        });
      }">x</button>
      <p class="self-center pl-2 text-lg">{{ artist.name }}</p>
    </div>
  </div>

  <form id="artist-search" v-on:submit="(event) => {
    event.preventDefault();
    this.getArtists(this.queryString);
  }">
    <label for="query">Artist Search</label>
    <input class="text-black" name="query" type="text" v-model="this.queryString">
    <button type="submit">Search</button>
  </form>

  <div id="artist-results" class="max-w-sm md:min-w-min md:max-w-md space-y-2">
    <div class="flex flex-row h-24 bg-neutral-700 hover:bg-gray-600 cursor-pointer p-2 rounded-md"
      v-for="artist in this.searchArtists" :key="artist.id" v-on:click="() => {
        this.selectedArtists.push(artist);
        this.searchArtists = [];
      }">
      <img class="rounded-full aspect-square" v-bind:src="artist.img" v-bind:alt="'Picture of ' + artist.name">
      <p class="self-center pl-6 text-lg">{{ artist.name }}</p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

type Artist = {
  id: string,
  name: string,
  img: string
}

export default defineComponent({
  data() {
    return {
      queryString: "",
      selectedArtists: [] as Artist[],
      searchArtists: [] as Artist[]
    }
  },
  methods: {
    addArtist(artist: Artist) {
      this.selectedArtists.push(artist);
    },
    async getArtists(query: string) {
      try {
        const res = await fetch(`/api/spotify/artists?query=${query}`);
        if (res.ok) {
          const txt = await res.text();
          const newJson = JSON.parse(txt);
          newJson.forEach((artist: Artist) => {
            this.searchArtists.push(artist);
          });
        }
      } catch (error) {
        console.log(error);
      }
    }
  }
});

</script>
