<template>
  <div>{{ processedImages}} / {{ total }}</div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  computed: mapState({
    inProgress: 'in_progress',
    total: 'total',
    processedImages: 'processed_images',
  }),
};
</script>
