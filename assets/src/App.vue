<template>
  <div class="wrap">
    <h1 class="wp-heading-inline">{{translations.dominant_color_async}}</h1>
    <div v-if="unprocessedImages !== 0 && !inProgress" class="notice notice-warning">
      <p>{{unprocessedImagesMessage}} <a href="#" v-on:click="processAll">{{translations.process}}</a></p>
    </div>
    <div class="dominant-color-async-postbox">
      <h2 class="dominant-color-async-postbox__heading">{{translations.processing_queue}}</h2>
      <div class="dominant-color-async-postbox__inside">
        <div class="dominant-color-async-postbox__status">
          <div class="dominant-color-async-postbox__status-circle" v-if="!inProgress"></div>
          <div class="dominant-color-async-postbox__status-circle dominant-color-async-postbox__status-circle--active" v-if="inProgress" v-html="require('!raw-loader!./ic-sync.svg')">
          </div>
          <div class="dominant-color-async-postbox__status-text">{{statusMessage}}</div>
        </div>
        <div class="dominant-color-async-postbox__progress">
          <div class="dominant-color-async-postbox__progress-bar" v-bind:style="{width: `${percentage}%`}" ></div>
        </div>
        <div class="dominant-color-async-postbox__count">
          <div>{{countMessage}}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { sprintf } from 'sprintf-js';
export default {
  computed: mapState({
    inProgress: 'in_progress',
    total: 'total',
    processedImages: 'processed_images',
    unprocessedImages: 'unprocessed_images',
    translations: 'translations',
    buttonLoading: 'buttonLoading',
    unprocessedImagesMessage(state) {
      return sprintf(
        this.translations.unprocessed_images_notice,
        this.unprocessedImages,
      );
    },
    countMessage(state) {
      return sprintf(
        this.translations.processed_images_count,
        this.processedImages,
        this.total,
      );
    },
    statusMessage(state) {
      if (state.in_progress) {
        return state.translations.processing;
      }
      return state.translations.not_in_progress;
    },
  }),
  data: () => {
    return {
      percentage: 0,
    };
  },
  watch: {
    processedImages: function(val) {
      this.percentage = (val / this.total) * 100;
    },
  },
  mounted() {
    this.$store.dispatch('getData');
  },
  methods: {
    processAll() {
      this.$store.dispatch('processAll');
    }
  }
};
</script>
