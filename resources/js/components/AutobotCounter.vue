<template>
  <div>
    <h2>Autobot Count: {{ count }}</h2>
  </div>
</template>

<script>
export default {
  data() {
    return {
      count: 0
    }
  },
  mounted() {
    this.getInitialCount();
    this.listenForUpdates();
  },
  methods: {
    getInitialCount() {
      axios.get('/api/autobot-count')
        .then(response => {
          this.count = response.data.count;
        });
    },
    listenForUpdates() {
      window.Echo.channel('autobots')
        .listen('AutobotCreated', (e) => {
          this.count = e.count;
        });
    }
  }
}
</script>
