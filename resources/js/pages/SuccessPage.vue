<template>
  <div class="flex flex-col justify-center items-center min-h-[50vh]">
    <div class="space-y-8">
      <div class="space-y-2">
        <p class="text-3xl font-medium">
          Added your new playlist
        </p>
        <p class="text-2xl text-purple-300">
          {{ this.name }}
        </p>
      </div>
      <button v-on:click="() => {
        this.newPlaylist();
      }" class="rounded-md bg-neutral-600 hover:bg-gray-500 px-4 py-2">Make another
        playlist</button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    name: String
  },
  methods: {
    newPlaylist() {
      document.location.assign('/dashboard');
    }
  }
});

</script>
