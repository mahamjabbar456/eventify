document.addEventListener('DOMContentLoaded', function() {
    const serviceType = document.getElementById('serviceType');
    const sittingType = document.getElementById('sittingType');
    const totalSeats = document.getElementById('totalSeats');
    const menuTextarea = document.getElementById('menu');
    const customFields = document.getElementById('customFields');
    const manualFields = document.getElementById('manualFields');
    const menuData = JSON.parse(document.getElementById('menuData').value);
    const includeAC = document.getElementById('includeAC');
    
    // Price display elements
    const orderPriceDisplay = document.getElementById('orderPriceDisplay');
    const discountDisplay = document.getElementById('discountDisplay');
    const acPriceDisplay = document.getElementById('acPriceDisplay');
    console.log(acPriceDisplay);
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const depositDisplay = document.getElementById('depositDisplay');
    
    // Function to update menu display based on service type
    function updateMenu() {
        switch(serviceType.value) {
            case 'sittingOnly':
                menuTextarea.value = "No menu included - sitting only";
                menuTextarea.readOnly = true;
                customFields.style.display = 'none';
                manualFields.style.display = 'block';
                break;
                
            case 'packageMenu':
                menuTextarea.value = "Standard package menu includes:\n" +
                    "Starter: " + (menuData.menuStarter || 'Not specified') + "\n" +
                    "Chicken: " + (menuData.menuChicken || 'Not specified') + "\n" +
                    "Beef: " + (menuData.menuBeef || 'Not specified') + "\n" +
                    "Mutton: " + (menuData.menuMutton || 'Not specified') + "\n" +
                    "Dessert: " + (menuData.menuDesert || 'Not specified') + "\n" +
                    "Drinks: " + (menuData.menuDrinks || 'Not specified') + "\n" +
                    "Salad: " + (menuData.menuSalad || 'Not specified') + "\n" +
                    "Additional Details: " + (menuData.menuDetail || 'None');
                menuTextarea.readOnly = true;
                customFields.style.display = 'none';
                manualFields.style.display = 'block';
                break;
                
            case 'customPackage':
                menuTextarea.value = '';
                menuTextarea.readOnly = false;
                menuTextarea.placeholder = 'Enter your custom menu requirements...';
                customFields.style.display = 'block';
                manualFields.style.display = 'none';
                break;
                
            default:
                menuTextarea.value = '';
                menuTextarea.readOnly = false;
                customFields.style.display = 'none';
                manualFields.style.display = 'none';
        }
    }
    
    // Function to update pricing
    function updatePricing() {
        if (serviceType.value === 'customPackage') {
            // For custom packages, show zeros (will be calculated later)
            orderPriceDisplay.textContent = '0';
            discountDisplay.textContent = '0';
            acPriceDisplay.textContent = '0';
            totalPriceDisplay.textContent = '0';
            depositDisplay.textContent = '0';
            return;
        }

        if (serviceType.value && sittingType.value) {
            let price = 0;
            let discount = parseFloat(menuData.discount) || 0;
            const seats = parseInt(totalSeats.value) || 0;
            
            // Get base price based on sitting type
            switch(sittingType.value) {
                case 'Sofa':
                    price = serviceType.value === 'sittingOnly' ? 
                           parseFloat(menuData.priceWithSofa) : 
                           parseFloat(menuData.priceWithSofaWithMenu);
                    break;
                case 'Chair':
                    price = serviceType.value === 'sittingOnly' ? 
                           parseFloat(menuData.priceWithChair) : 
                           parseFloat(menuData.priceWithChairWithMenu);
                    break;
                case 'Mix':
                    price = serviceType.value === 'sittingOnly' ? 
                           parseFloat(menuData.priceWithMix) : 
                           parseFloat(menuData.priceWithMixWithMenu);
                    break;
            }
            
            // Calculate AC charges
            let acCharges = 0;
            if (includeAC.value === '1') {  // Changed to compare with '1' to match your HTML
                const acRate = parseFloat(menuData.acChargesPrices) || 0;
                const acDetail = menuData.acRates === 'perPerson' ? 'per Person': 'per Hour';
                console.log(acDetail);
                if (acDetail === 'per Person') {
                    acCharges = acRate * seats;
                } else {
                    acCharges = acRate * 3; // Flat rate for high AC charges
                }
            }
            
            const orderPrice = price * seats + acCharges;
            const finalDiscount = orderPrice * discount / 100;
            const totalPrice = orderPrice - finalDiscount;
            const deposit = totalPrice * 0.25;
            
            // Update displays
            orderPriceDisplay.textContent = orderPrice.toFixed(2);
            discountDisplay.textContent = discount.toFixed(2);
            acPriceDisplay.textContent = acCharges.toFixed(2); // Show actual amount, not percentage
            totalPriceDisplay.textContent = totalPrice.toFixed(2);
            depositDisplay.textContent = deposit.toFixed(2);
        } else {
            // Reset if not enough data
            orderPriceDisplay.textContent = '0';
            discountDisplay.textContent = '0';
            acPriceDisplay.textContent = '0';
            totalPriceDisplay.textContent = '0';
            depositDisplay.textContent = '0';
        }
    }
    
    // Combined update function
    function updateAll() {
        updateMenu();
        updatePricing();
    }
    
    // Add event listeners
    serviceType.addEventListener('change', updateAll);
    sittingType.addEventListener('change', updateAll);
    totalSeats.addEventListener('input', updatePricing);
    includeAC.addEventListener('change', updatePricing);
    
    // Initial update
    updateAll();
});

$(document).ready(function() {
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
    $('input[name="cnic"]').inputmask({
        mask: '99999-9999999-9',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});