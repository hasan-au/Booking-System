<script setup>
</script>
<template>
    <!-- Hero -->
  <section class="relative overflow-hidden pt-20 min-h-screen flex items-center">
    <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary-900/20 via-transparent to-transparent"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-32 grid lg:grid-cols-2 gap-16 items-center">
      <div>
        <div class="inline-flex items-center gap-2 bg-primary-500/10 border border-primary-500/20 rounded-full px-4 py-2 text-sm text-primary-300 mb-8">
          <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
          Now Open - Book Your Appointment
        </div>
        <h1 class="text-5xl lg:text-7xl font-black leading-tight text-gray-100">
          Sharp Cuts. <br>
          <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Premium Style.</span><br>
          <span class="text-3xl lg:text-5xl font-semibold text-gray-400">Book in Seconds.</span>
        </h1>
        <p class="mt-8 text-xl text-gray-300 leading-relaxed max-w-lg">
          Experience the finest men's grooming with precision cuts, expert beard styling, and luxury treatments.
          Our smart booking system makes scheduling effortless.
        </p>
        <div class="mt-12 flex flex-col sm:flex-row gap-4">
          <a href="#booking" class="group px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white rounded-xl font-semibold hover:shadow-2xl hover:shadow-primary-500/25 transition-all text-center">
            <span class="group-hover:translate-x-1 transition-transform inline-block">Start Booking →</span>
          </a>
          <a href="#services" class="px-8 py-4 border-2 border-gray-700 text-gray-300 rounded-xl font-semibold hover:bg-gray-800 hover:border-primary-500/50 transition-all text-center">View Services</a>
        </div>

        <!-- Trust Badges -->
        <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="p-4 bg-gray-900/50 border border-gray-800 rounded-xl text-center backdrop-blur">
            <div class="text-2xl mb-2">✂️</div>
            <div class="text-sm font-medium text-gray-200">Sterilized Tools</div>
          </div>
          <div class="p-4 bg-gray-900/50 border border-gray-800 rounded-xl text-center backdrop-blur">
            <div class="text-2xl mb-2">⏰</div>
            <div class="text-sm font-medium text-gray-200">On-Time Service</div>
          </div>
          <div class="p-4 bg-gray-900/50 border border-gray-800 rounded-xl text-center backdrop-blur">
            <div class="text-2xl mb-2">⭐</div>
            <div class="text-sm font-medium text-gray-200">4.9/5 Rating</div>
          </div>
          <div class="p-4 bg-gray-900/50 border border-gray-800 rounded-xl text-center backdrop-blur">
            <div class="text-2xl mb-2">🏆</div>
            <div class="text-sm font-medium text-gray-200">Award Winning</div>
          </div>
        </div>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-tr from-primary-600/30 to-transparent rounded-3xl transform rotate-3"></div>
        <div class="absolute -inset-4 bg-gradient-to-r from-primary-600/20 to-primary-400/20 rounded-3xl blur-xl"></div>
        <img alt="Modern barbershop interior with professional setup" class="relative w-full h-96 lg:h-[600px] object-cover rounded-3xl shadow-2xl"
             src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&w=1600&auto=format&fit=crop"/>
      </div>
    </div>
  </section>
</template>
