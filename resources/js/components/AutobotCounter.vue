<template>
  <div class="autobot-counter">
    <h1>Total Autobots Created: {{ count }}</h1>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  setup() {
    /**
     * The count of autobots that have been created, initially set to 0.
     *
     * @type {Ref<number>}
     */
    const count = ref(0);

    onMounted(() => {
      // Fetch the initial count of autobots from the API
      fetchInitialCount();

      // Subscribe to the Echo channel and listen for AutobotCreated events
      subscribeToUpdates();

      //   Fetch the count every hour
      setInterval(fetchInitialCount, 3600000);
    });

    /**
     * Fetches the initial count of autobots from the API.
     *
     * @return {Promise<void>}
     */
    const fetchInitialCount = async () => {
      // Fetch the count from the API
      const response = await fetch("/api/autobots/count");

      // Parse the JSON response
      const data = await response.json();

      // Update the count
      count.value = data.count;
    };

    /**
     * Subscribes to the Echo channel for autobots and updates the count whenever
     * an AutobotCreated event is received.
     *
     * @return {void}
     */
    const subscribeToUpdates = () => {
      // Subscribe to the Echo channel
      window.Echo.channel("autobots").listen("AutobotCreated", (event) => {
        // Event contains the updated count
        count.value = event.count;
      });
    };

    // Return the count
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
