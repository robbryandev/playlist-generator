<template>
  <div class="flex flex-col justify-center items-center h-[80vh]">
    <TypedHeader v-bind:phrases="['Your next playlist starts here', 'generate a new spotify playlist now']" />
    <a v-if="isAuth" href="{{ url('/dashboard') }}"
      class="font-semibold hover:text-neutral-200 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm border-b-2 border-b-purple-400 hover:border-b-purple-600 pb-1">New
      Playlist</a>
    <a v-else href="/api/auth/redirect"
      class="font-semibold hover:text-neutral-200 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm border-b-2 border-b-purple-400 hover:border-b-purple-600 pb-1">Login
      with spotify</a>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import TypedHeader from '../components/TypedHeader.vue';

export default defineComponent({
  props: {
    isAuthStr: String
  },
  data() {
    return {
      isAuth: this.isAuthStr === 'true'
    }
  }
});

</script>
