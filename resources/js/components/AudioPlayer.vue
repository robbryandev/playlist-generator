<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    src: String,
    song: String,
    artist: String
  },
  data() {
    return {
      playing: false,
      percentage: 0,
      duration: 0,
      currentTime: 0
    }
  },
  methods: {
    play() {
      this.$refs.audioPlayer.play();
      this.playing = true;
    },
    pause() {
      this.$refs.audioPlayer.pause();
      this.playing = false;
    }
  },
  expose: ['play'],
  mounted() {
    setInterval(() => {
      if (this.playing && this.$refs.audioPlayer.currentTime && this.$refs.audioPlayer.duration) {
        this.percentage = (this.$refs.audioPlayer.currentTime / this.$refs.audioPlayer.duration) * 100;
        if (this.percentage === 100) {
          this.playing = false;
          this.percentage = 0;
        }
      }
    }, 500)
  }
})
</script>

<template>
  <div v-if="this.song" class="flex flex-col">
    <div class="flex flex-row gap-2">
      <p>{{ this.song }}</p>
      <p>-</p>
      <p>{{ this.artist }}</p>
    </div>
    <div class="flex flex-row">
      <audio v-bind:src="this.src" ref="audioPlayer"></audio>
      <button v-if="!this.playing" v-on:click="() => {
        this.play();
      }">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-auto">
          <path fill-rule="evenodd"
            d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm14.024-.983a1.125 1.125 0 010 1.966l-5.603 3.113A1.125 1.125 0 019 15.113V8.887c0-.857.921-1.4 1.671-.983l5.603 3.113z"
            clip-rule="evenodd" />
        </svg>
      </button>
      <button v-else v-on:click="() => {
        this.pause();
      }">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-auto">
          <path fill-rule="evenodd"
            d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zM9 8.25a.75.75 0 00-.75.75v6c0 .414.336.75.75.75h.75a.75.75 0 00.75-.75V9a.75.75 0 00-.75-.75H9zm5.25 0a.75.75 0 00-.75.75v6c0 .414.336.75.75.75H15a.75.75 0 00.75-.75V9a.75.75 0 00-.75-.75h-.75z"
            clip-rule="evenodd" />
        </svg>
      </button>
      <div class="bg-neutral-400 w-44 h-4 mt-1 ml-2">
        <div class="h-full m-0 p-0 bg-purple-400" v-bind:style="'width:' + Math.floor(this.percentage) + '%'"></div>
      </div>
    </div>
  </div>
</template>