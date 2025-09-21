<script setup>

defineProps({
    employees: Array,
});

</script>
<template>
    <section id="team" class="py-32 bg-gray-900/30">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-20">
        <h2 class="text-4xl lg:text-6xl font-bold text-gray-100 mb-6">Meet Our Team</h2>
        <p class="text-xl text-gray-400 max-w-3xl mx-auto">Choose your preferred barber during booking. Our master stylists bring years of expertise and passion to every cut.</p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <!-- Members -->
        <div v-for="employee in employees" class="gradient-border hover:scale-105 transition-all duration-500 h-full">
          <div class="gradient-border-content p-8 h-full flex flex-col">
            <div class="flex items-center gap-6 mb-6">
              <img :src="employee.photo" :alt="employee.name" class="w-20 h-20 rounded-full object-cover border-2 border-primary-500/30"/>
              <div>
                <h3 class="font-bold text-xl text-gray-100">{{ employee.name }}</h3>
                <div class="text-sm text-gray-300">{{ employee.job_title }}</div>
                <div class="text-sm text-primary-400">⭐ {{ employee.rating }}/5</div>
              </div>
            </div>
            <p class="text-gray-400 leading-relaxed mb-4">{{ employee.bio }}</p>
            <div class="text-xs text-gray-500"><strong class="text-gray-400">Specializes in:</strong> {{ employee.job_title }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
