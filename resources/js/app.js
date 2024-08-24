import { createApp } from "vue";
import AutobotCounter from "./components/AutobotCounter.vue";
import "./bootstrap";

const app = createApp({});
app.component("autobot-counter", AutobotCounter);
app.mount("#app");
