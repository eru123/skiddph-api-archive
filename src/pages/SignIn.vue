<script setup>
import { ref } from 'vue'

const step = ref(1)

</script>
<template>
    <v-container fluid>
        <v-row align="center" justify="center">
            <v-col cols="12" sm="10">
                <v-row>
                    <v-col cols="12" md="6">
                        <v-card-text class="mt-12">
                            <h4 class="text-center">Login in to Your Account</h4>
                            <h6 class="text-center  grey--text ">Log in to your account so you can continue
                                builiding <br>and editing your onboarding flows</h6>
                            <v-row align="center" justify="center">
                                <v-col cols="12" sm="8">

                                    <v-text-field label="Email" outlined dense color="blue" autocomplete="false"
                                        class="mt-16" />
                                    <v-text-field label="Password" outlined dense color="blue" autocomplete="false"
                                        type="password" />
                                    <v-row>
                                        <v-col cols="12" sm="7">
                                            <v-checkbox label="Remember Me" class="mt-n1" color="blue" dark>
                                            </v-checkbox>
                                        </v-col>
                                        <v-col cols="12" sm="5">
                                            <span class="caption blue--text">Forgot password</span>
                                        </v-col>
                                    </v-row>
                                    <v-btn color="blue" dark block tile>Log in</v-btn>

                                    <h5 class="text-center  grey--text mt-4 mb-3">Or Log in using</h5>
                                    <div class="d-flex  justify-space-between align-center mx-10 mb-16">
                                        <v-btn depressed outlined color="grey">
                                            <v-icon color="red">fab fa-google</v-icon>
                                        </v-btn>
                                        <v-btn depressed outlined color="grey">
                                            <v-icon color="blue">fab fa-facebook-f</v-icon>
                                        </v-btn>
                                        <v-btn depressed outlined color="grey">
                                            <v-icon color="light-blue lighten-3">fab fa-twitter</v-icon>
                                        </v-btn>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-col>
                    <v-col cols="12" md="6" class="blue rounded-bl-xl hidden md:block">
                        <div style="  text-align: center; padding: 180px 0;">
                            <v-card-text class="white--text">
                                <h3 class="text-center ">Don't Have an Account Yet?</h3>
                                <h6 class="text-center">Let's get you all set up so you can start creating
                                    your your first<br> onboarding experience</h6>
                            </v-card-text>
                            <div class="text-center">
                                <v-btn tile outlined dark @click="step++">SIGN UP</v-btn>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>
