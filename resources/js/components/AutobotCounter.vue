<template>
  <div class="autobot-counter">
    <h1>Total Autobots Created: {{ count }}</h1>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  setup() {
    const count = ref(0);

    onMounted(() => {
      fetchInitialCount();
      subscribeToUpdates();
    });

    const fetchInitialCount = async () => {
      const response = await fetch("/api/autobots/count");
      const data = await response.json();
      count.value = data.count;
    };

    const subscribeToUpdates = () => {
      console.log("Subscribing to updates...");
      window.Echo.channel("autobots").listen("AutobotCreated", (event) => {
        count.value = event.count;
      });
    };

    return { count };
  },
};
</script>

<style scoped>
.autobot-counter {
  text-align: center;
  font-size: 2rem;
  margin-top: 20px;
}
</style>
