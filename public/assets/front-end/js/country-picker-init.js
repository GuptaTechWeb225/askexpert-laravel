        
 const input = document.querySelector("#phone");
const iti = window.intlTelInput(input, {
    initialCountry: "ca",
    separateDialCode: true,
    preferredCountries: ["in", "us", "ca"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
});