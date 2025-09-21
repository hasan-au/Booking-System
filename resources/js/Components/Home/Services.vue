<script setup>
import { Icon } from '@iconify/vue';
defineProps({
  services: {
    type: Array,
    required: true
  }
});
</script>
<template>
    <section id="services" class="py-32 bg-gray-950">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-20">
        <h2 class="text-4xl lg:text-6xl font-bold text-gray-100 mb-6">Services & Prices</h2>
        <p class="text-xl text-gray-400 max-w-3xl mx-auto">Professional grooming services at competitive rates with premium quality guaranteed</p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <!-- Service cards (static) -->
        <div v-for="service in services" :key="service.id"  class="service-card gradient-border">
          <div class="gradient-border-content p-8">
            <div class="flex items-start justify-between gap-4 mb-6">
              <div class="text-4xl"><Icon :icon="service.icon" width="35" height="35" /></div>
              <div class="text-right">
                <div class="text-3xl font-bold text-primary-400">${{ service.price }}</div>
                <div class="text-sm text-gray-500">~{{ service.duration_minutes }} minutes</div>
              </div>
            </div>
            <h3 class="font-bold text-xl text-gray-100 mb-3">{{ service.name }}</h3>
            <p class="text-gray-400 leading-relaxed">{{ service.description }}</p>
          </div>
        </div>
      </div>

      <div class="text-center">
        <a href="#booking" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-white font-semibold hover:shadow-2xl hover:shadow-primary-500/25 transition-all">
          Book Service Now
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
          </svg>
        </a>
      </div>
    </div>
  </section>
</template>
