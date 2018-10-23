<template>
  <div class="wrap">
    <h1 class="wp-heading-inline">{{translations.dominant_color_async}}</h1>
    <div v-if="unprocessedImages !== 0" class="notice notice-warning">
      <p>{{unprocessedImagesMessage}} <a href="#">{{translations.process}}</a></p>
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
    unprocessedImagesMessage(state) {
      return sprintf(
        this.translations.unprocessed_images_notice,
        this.unprocessedImages
      );
    },
  }),
};
</script>
