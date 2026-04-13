function r(t){const m=typeof t=="number"?t:Number(t);return Number.isNaN(m)?"-":`Rs. ${new Intl.NumberFormat("en-IN",{minimumFractionDigits:2,maximumFractionDigits:2}).format(m)}`}export{r as f};
