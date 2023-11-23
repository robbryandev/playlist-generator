<template>
  <div v-if="!this.showPlaylist" class="flex flex-col justify-center items-center w-full h-[80vh] gap-4">
    <div id="artist-picked" class="max-w-sm md:min-w-min md:max-w-md space-y-2">
      <div class="flex flex-row h-24 bg-neutral-700 p-2 rounded-md" v-for="artist in this.selectedArtists"
        :key="artist.id">
        <img class="rounded-full aspect-square" v-bind:src="artist.img" v-bind:alt="'Picture of ' + artist.name">
        <button class="bg-red-500 rounded-full w-6 h-6 font-semibold self-center ml-4" v-on:click="() => {
          this.selectedArtists = this.selectedArtists.filter((fArtist) => {
            return fArtist.id != artist.id;
          });
        }">x</button>
        <p class="self-center pl-2 text-lg">{{ artist.name }}</p>
      </div>
    </div>

    <form v-if="this.selectedArtists.length != 5" class="w-1/4" id="artist-search" v-on:submit="(event) => {
      event.preventDefault();
      this.searchArtists = [];
      this.getArtists(this.queryString);
    }">
      <div class="flex flex-row gap-0 justify-center">
        <input class="text-black px-4 py-2 rounded-l-md" placeholder="Artist Search" name="query" type="text"
          v-model="this.queryString">
        <button class="rounded-r-md bg-neutral-600 hover:bg-gray-500 place-self-center self-stretch px-4"
          type="submit">Search</button>
      </div>
    </form>

    <button v-else v-on:click="() => {
      this.getPlaylist();
    }">
      Generate Playlist
    </button>

    <div id="artist-results" class="min-w-min max-w-md space-y-2">
      <div class="flex flex-row h-24 bg-neutral-700 hover:bg-gray-600 cursor-pointer py-2 px-4 rounded-md"
        v-for="artist in   this.searchArtists  " :key="artist.id" v-on:click="() => {
          this.selectedArtists.push(artist);
          this.queryString = '';
          this.searchArtists = [];
        }">
        <img class="rounded-full aspect-square" v-bind:src="artist.img" v-bind:alt="'Picture of ' + artist.name">
        <p class="self-center pl-6 text-lg float-left">{{ artist.name }}</p>
      </div>
    </div>
  </div>
  <div v-else class="flex flex-col justify-center items-center w-full h-[80vh] gap-4">
    <p>Put playlist info here</p>
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
      showPlaylist: false,
      queryString: "",
      selectedArtists: [] as Artist[],
      searchArtists: [] as Artist[]
    }
  },
  methods: {
    async getArtists(query: string) {
      if (query === "") {
        return
      }
      try {
        const res = await fetch(`/api/artists?query=${query}`);
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
    },
    async getPlaylist() {
      const currentArtists = this.selectedArtists.map((artist: Artist) => {
        return artist.id;
      }).join(",");
      try {
        const playlistRes = await fetch(`/api/playlist/new?artists=${currentArtists}`);
        if (playlistRes.ok) {
          const txtRes = await playlistRes.text();
          this.showPlaylist = true;
          console.log(txtRes);
        }
      } catch (error) {
        console.log(error);
      }
    }
  }
});

</script>
