<script setup>
defineProps({
    testimonials: Array,
});
</script>
<template>
    <section id="testimonials" class="py-32 bg-gray-950">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-20">
        <h2 class="text-4xl lg:text-6xl font-bold text-gray-100 mb-6">What Our Clients Say</h2>
        <p class="text-xl text-gray-400 max-w-3xl mx-auto">Real feedback from satisfied customers who trust us with their style</p>
      </div>
      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Testimonial 1 -->
        <div v-for="testimonial in testimonials" :key="testimonial.id" class="gradient-border hover:scale-105 transition-all duration-500 h-full">
          <div class="gradient-border-content p-8 h-full flex flex-col">
            <div class="mb-6">
              <div class="text-primary-400 text-xl mb-4"><span v-for="star in testimonial.rating" :key="star">★</span></div>
              <p class="text-gray-300 text-lg leading-relaxed italic">"{{ testimonial.content }}"</p>
            </div>
            <div class="border-t border-gray-700 pt-6 flex justify-between items-center">
              <div class="font-semibold text-gray-100">{{ testimonial.name }}</div>
              <div class="text-xs text-gray-400 bg-gray-800 px-3 py-1 rounded-full">{{ testimonial.service.name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
