<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch, defineProps, ref, watchEffect } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import { useToast } from "vue-toastification";
import axios from 'axios';
import { Block } from 'notiflix/build/notiflix-block-aio';
import { Loading } from 'notiflix/build/notiflix-loading-aio';
import { Icon } from '@iconify/vue';

const props = defineProps({
    employees: Array,
    services: Array,
    minDate: String,
    maxDate: String,
});

const employeesOfService = computed(() => {
    // return props.employees.filter(emp => emp.services.includes(BookingForm.step1.service_id));
    return props.services.find(service => service.id === BookingForm.step1.service_id)?.employees || [];
});

const disabledDates = computed(() => {
    const employee = props.employees?.find(emp => emp.id == BookingForm.step2.employee_id);
    const rawDaysOff = employee?.all_days_off_as_date;

    if (!rawDaysOff) return [];

    let cleanDates = [];

    if (Array.isArray(rawDaysOff)) {

        // console.log('Processing array:', rawDaysOff);
        cleanDates = rawDaysOff.filter(date => date != null && date !== '');
    } else if (typeof rawDaysOff === 'object') {

        // console.log('Processing object:', rawDaysOff);
        cleanDates = Object.values(rawDaysOff).filter(date => date != null && date !== '');
    }

    // console.log('Clean dates:', cleanDates);

    // Convert to Date objects and filter invalid dates
    const validDates = cleanDates
        .map(date => new Date(date))
        .filter(date => !isNaN(date.getTime()));

    // console.log('Valid dates:', validDates);

    return validDates;
});

const disabledDays = computed(() => {
    return BookingForm.step2.employee_id ? props.employees.find(emp => emp.id == BookingForm.step2.employee_id)?.weekly_days_off || [] : []; // If no employee selected,
});

const bookingSummary = computed(() => {
    const service = props.services.find(svc => svc.id === BookingForm.step1.service_id);
    const employee = props.employees.find(emp => emp.id === BookingForm.step2.employee_id);
    return {
        serviceName: service ? service.name : '',
        servicePrice: service ? service.price : 0,
        serviceDuration: service ? service.duration_minutes : 0,
        employeeName: employee ? employee.name : '',
        date: BookingForm.step3.date,
        time: BookingForm.step3.datetime,
    };
});
const availableTimes = ref([]); // To store available times fetched from the server
const step = ref(1);
const isTransitioning = ref(false); // Add transition state
const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;



const enableTimeTable = computed(() => {
    return availableTimes.value.length < 0;
});

const BookingForm = useForm({
    step1:{
        service_id: '',
    },
    step2:{
        employee_id: '',
    },
    step3:{
        date: '',
        datetime: '',
    },
    step4:{
        customer_name: '',
        customer_email: '',
        customer_phone: '',
    }
});

function nextStep(){
    // const currentStep = step.value;
    if(step.value === 1 && !BookingForm.step1.service_id){
        useToast().error("Please select a service to proceed.");
        return;
    }

    if(step.value === 2 && !BookingForm.step2.employee_id){
        useToast().error("Please select an employee to proceed.");
        return;
    }

    if(step.value === 3 && (!BookingForm.step3.date || !BookingForm.step3.datetime)){
        useToast().error("Please select a date and time to proceed.");
        return;
    }
    step.value++;
    // Add transition effect
    isTransitioning.value = true;
    setTimeout(() => {
        isTransitioning.value = false;
    }, 150); // Short delay for smooth transition
}

function prevStep(){

    if(step.value <= 4 ){
        BookingForm.step4.customer_name = '';
        BookingForm.step4.customer_email = '';
        BookingForm.step4.customer_phone = '';
    }
    if(step.value <= 3){
        BookingForm.step3.date = '';
        BookingForm.step3.datetime = '';
        availableTimes.value = [];
    }

    if(step.value <= 2){
        BookingForm.step2.employee_id = '';
    }
    if(step.value === 1){
        return;
    }
    step.value--;
    // Add transition effect
    isTransitioning.value = true;
    setTimeout(() => {
        isTransitioning.value = false;
    }, 150);

}

function resetForm(){
    BookingForm.step1.service_id = '';
    BookingForm.step2.employee_id = '';
    BookingForm.step3.date = '';
    BookingForm.step3.datetime = '';
    BookingForm.step4.customer_name = '';
    BookingForm.step4.customer_email = '';
    BookingForm.step4.customer_phone = '';

    step.value = 1;
}

const handleDate = async (modelData) => {
    BookingForm.step3.date = modelData;
    try{
        const response = await axios.get(route('check-availability'), {
            params: {
                employee_id: BookingForm.step2.employee_id,
                service_id: BookingForm.step1.service_id,
                date: BookingForm.step3.date,
            }
        });
        availableTimes.value = response.data.available_slots || [];
        console.log('Available times:', availableTimes.value);
        if(availableTimes.value.length === 0){
            useToast().info("No available time slots for the selected date. Please choose another date.");
        }
    }catch(error){
        console.error('Error fetching available times:', error);
        availableTimes.value = [];
        useToast().error("Error fetching available times.");
    }
}

const submitForm = () => {
        BookingForm.post(route('book-appointment'), {
        preserveScroll: true,
        onStart: () => {
            Block.circle('#booking', 'Booking your appointment . . .', {
                backgroundColor: 'rgba(13, 17, 23, 0.8)',
                messageColor: '#ffffff',
                svgColor: '#ffffff',
                fontSize: '16px',
                borderRadius: '8px',
            });
        },
        onSuccess: (response) => {
            // console.log('Form submitted successfully:', response);
            useToast().success("Booking confirmed! We look forward to seeing you.");
            resetForm();
            step.value = 1;
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            useToast().error("There were errors with your submission. Please check the form and try again.");

            for (const [field, val] of Object.entries(errors || {})) {
                const messages = Array.isArray(val) ? val : [val]

                messages.forEach(msg => {
                useToast().error(`${msg}`)
                })
            }

        },
        onFinish: () => {
            console.log('Form submission process finished.');
            Block.remove('#booking');
        }
    });
}

watch(BookingForm, (newVal)=>{
    console.log(newVal.step1.service_id, newVal.step2.employee_id, newVal.step3.date, newVal.step3.datetime, newVal.step4.customer_name, newVal.step4.customer_email, newVal.step4.customer_phone);
    console.log(step.value);

}, {deep:true})

watch(step, (newStep)=>{
    if(newStep === 1){
        resetForm();
    }
})

console.log(usePage().props);

</script>
<template>
    <section id="booking" class="py-32 bg-gradient-to-br from-gray-900 via-gray-850 to-gray-900">
    <div class="max-w-5xl mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="text-4xl lg:text-6xl font-bold text-gray-100 mb-6">Book Your Appointment</h2>
        <p class="text-xl text-gray-400">Quick and easy 4-step booking process</p>
        <p class="text-lg text-gray-300 mt-4 mb-8">Any Issue while booking? <a href="#" class="text-primary-500">Contact Support Or Call Us Now: 1700 100 100</a></p>
      </div>

      <!-- Progress Steps (static styling) -->
      <div class="flex items-center justify-center gap-4 mb-16 flex-wrap">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full flex items-center justify-center font-semibold" :class="{'bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-xl': step >= 1}">1</div>
          <span class="text-gray-300 font-medium">Service</span>
        </div>
        <div class="h-px w-12 bg-gray-600 hidden sm:block"></div>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 border-gray-600 font-semibold" :class="{'bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-xl': step >= 2}">2</div>
          <span class="text-gray-300 font-medium">Barber</span>
        </div>
        <div class="h-px w-12 bg-gray-600 hidden sm:block"></div>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 border-gray-600 font-semibold" :class="{'bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-xl': step >= 3}">3</div>
          <span class="text-gray-300 font-medium">Date &amp; Time</span>
        </div>
        <div class="h-px w-12 bg-gray-600 hidden sm:block"></div>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 border-gray-600 font-semibold" :class="{'bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-xl': step >= 4}">4</div>
          <span class="text-gray-300 font-medium">Confirm</span>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="space-y-10">
        <Transition name="slide-fade" mode="out-in">
        <!-- Step 1: Service (static grid) -->
         <!-- <Transition name="slide-fade" mode="out-in"> -->
        <div v-if="step === 1">
          <h3 class="text-2xl font-semibold mb-8 text-gray-100">Choose Your Service</h3>
          <div class="grid sm:grid-cols-2 gap-6">
            <!-- 6 service choices as non-interactive blocks -->
            <button v-for="service in services" :key="service.id" @click="BookingForm.step1.service_id = service.id" type="button" class="p-6 rounded-xl bg-gray-800/50 border border-gray-700 text-left hover:bg-gray-800 hover:border-primary-500/50 transition-all focus:ring-2 focus:ring-primary-500">
              <div class="flex items-start gap-4">
                <div class="text-3xl"><Icon :icon="service.icon" width="35" height="35" /></div>
                <div class="flex-1">
                  <div class="font-semibold text-lg mb-2 text-gray-100">{{ service.name }}</div>
                  <div class="text-sm text-gray-400 mb-3 leading-relaxed">{{ service.description }}</div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Duration: {{ service.duration_minutes }} min</span>
                    <span class="font-bold text-2xl text-primary-400">${{ service.price }}</span>
                  </div>
                </div>
              </div>
            </button>
          </div>
        </div>
        <!-- </Transition> -->

        <!-- Step 2: Staff (static) -->
        <!-- <Transition name="slide-fade" mode="out-in"> -->
        <div v-else-if="step === 2">
          <h3 class="text-2xl font-semibold mb-8 text-gray-100">Select Your Barber</h3>
          <div class="grid sm:grid-cols-2 gap-6">

            <button @click="BookingForm.step2.employee_id = employee.id" type="button" v-for="employee in employeesOfService" :key="employee.id" class="p-6 rounded-xl bg-gray-800/50 border border-gray-700 text-left hover:bg-gray-800 hover:border-primary-500/50 transition-all focus:ring-2 focus:ring-primary-500" :class="{'ring-2 ring-primary-500': BookingForm.step2.employee_id === employee.id}">
              <div class="flex items-center gap-4">
                <img :src="employee.photo" class="w-16 h-16 rounded-full object-cover border-2 border-gray-600" :alt="employee.name"/>
                <div class="flex-1">
                  <div class="font-semibold text-lg text-gray-100">{{ employee.name }}</div>
                  <div class="text-sm text-gray-300">{{ employee.job_title }}</div>
                  <div class="text-sm text-primary-400">⭐ {{ employee.rating }}/5 • Hours: {{ employee.work_start_time }}–{{ employee.work_end_time }}</div>
                </div>
              </div>
            </button>
          </div>
          <p class="text-sm text-gray-400 mt-6">Only barbers qualified for your selected service are shown. (Static preview)</p>
        </div>
        <!-- </Transition> -->

        <!-- Step 3: Date & Time (static) -->
        <!-- <Transition name="slide-fade" mode="out-in"> -->
        <div v-else-if="step === 3">
          <h3 class="text-2xl font-semibold mb-8 text-gray-100">Pick Date &amp; Time</h3>
          <div class="grid sm:grid-cols-2 gap-0 space-y-6 sm:space-y-0 sm:gap-6">
            <div>
              <label class="block mb-4 font-medium text-gray-300">Date</label>
              <VueDatePicker :min-date="minDate" :max-date="maxDate" :model-value="BookingForm.step3.date" @update:model-value="handleDate" :disabled-week-days="disabledDays" :disabled-dates="disabledDates" :enable-time-picker="false" :dark="true" :timezone="tz" auto-apply inline class="dp-wide" input-class="w-full px-4 py-4 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"/>
              <!-- <input id="dateOfBooking" type="date" :min="minDate" :max="maxDate" class="w-full px-4 py-4 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" /> -->
            </div>
            <div>
              <label class="block mb-4 font-medium text-gray-300">Time</label>
              <select :disabled="enableTimeTable" v-model="BookingForm.step3.datetime" size="12" class="w-full px-4 py-4 rounded-sm bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option disabled>Select time</option>
                <option v-for="time in availableTimes" :key="time" :value="time">{{ time }}</option>
              </select>
            </div>
          </div>
          <p class="text-sm text-gray-400 mt-6">Available times depend on your selected barber's schedule. (Static preview)</p>
        </div>
        <!-- </Transition> -->

        <!-- Step 4: Confirm (static) -->
        <!-- <Transition name="slide-fade" mode="out-in"> -->
        <div v-else-if="step === 4">
          <h3 class="text-2xl font-semibold mb-8 text-gray-100">Review &amp; Confirm</h3>
          <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8 mb-8 backdrop-blur">
            <div class="space-y-4">
              <div class="flex justify-between items-center py-3 border-b border-gray-700">
                <span class="text-gray-400">Service:</span>
                <span class="font-semibold text-gray-100">{{ bookingSummary.serviceName }}</span>
              </div>
              <div class="flex justify-between items-center py-3 border-b border-gray-700">
                <span class="text-gray-400">Price:</span>
                <span class="font-semibold text-2xl text-primary-400">{{ bookingSummary.servicePrice }} AUD</span>
              </div>
              <div class="flex justify-between items-center py-3 border-b border-gray-700">
                <span class="text-gray-400">Duration:</span>
                <span class="font-semibold text-gray-100">{{ bookingSummary.serviceDuration }} minutes</span>
              </div>
              <div class="flex justify-between items-center py-3 border-b border-gray-700">
                <span class="text-gray-400">Barber:</span>
                <span class="font-semibold text-gray-100">{{ bookingSummary.employeeName }}</span>
              </div>
              <div class="flex justify-between items-center py-3 border-b border-gray-700">
                <span class="text-gray-400">Date:</span>
                <span class="font-semibold text-gray-100">{{ new Intl.DateTimeFormat('en', { day: '2-digit', month: 'long', year: 'numeric', timeZone: tz }).format(bookingSummary.date) }}</span>
              </div>
              <div class="flex justify-between items-center py-3">
                <span class="text-gray-400">Time:</span>
                <span class="font-semibold text-primary-400 text-xl">{{ bookingSummary.time.trim().split(' ')[1] }}</span>
              </div>
            </div>
          </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <input v-model="BookingForm.step4.customer_name" class="px-4 py-4 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Full Name" />
            <input v-model="BookingForm.step4.customer_email" class="px-4 py-4 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Email Address" />
            <input v-model="BookingForm.step4.customer_phone" class="px-4 py-4 rounded-xl bg-gray-800 text-gray-100 border border-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Phone Number" />
        </div>
          <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-white font-semibold hover:shadow-xl hover:shadow-primary-500/25 transition-all" >
            Confirm Booking
          </button>
        </div>
        </Transition>

        <!-- Navigation -->
        <div class="flex gap-4 pt-8 border-t border-gray-700">
            <button id="prevBtn" v-if="step > 1" @click="prevStep" type="button" class="px-6 py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-800 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                Previous
            </button>
            <button id="nextBtn" v-if="step < 4" @click="nextStep"  type="button" class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-white font-semibold hover:shadow-xl hover:shadow-primary-500/25 transition-all">
                Next
            </button>
        </div>
      </form>
    </div>
  </section>
</template>


<style scoped>
/* Slide fade transition for step changes */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease-in-out;
}

.slide-fade-enter-from {
  transform: translateX(30px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(-30px);
  opacity: 0;
}

/* Keyframe animations */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(0px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0px);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

.animate-slide-up {
  animation: slide-up 0.6s ease-out;
  animation-fill-mode: both;
}

/* Loading state for buttons */
.loading {
  position: relative;
  color: transparent;
}

.loading::after {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  top: 50%;
  left: 50%;
  margin-left: -8px;
  margin-top: -8px;
  border-radius: 50%;
  border: 2px solid transparent;
  border-top-color: currentColor;
  animation: spin 1s ease infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}



:deep(.dp__menu.dp__theme_dark) { width: 25rem;height: 17.5rem;background-color: rgb(31 41 55 / var(--tw-bg-opacity, 1)); }
</style>
