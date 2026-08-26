if (typeof Swal !== 'undefined') {
    const originalSwalFire = Swal.fire;
    
    Swal.fire = function() {
        let options = arguments[0];
        
        if (typeof options === 'string') {
            options = { title: arguments[0], text: arguments[1], icon: arguments[2] };
        } else {
            options = Object.assign({}, options);
        }

        const hasCustomPopup = options.customClass && options.customClass.popup;
        const isAlreadyModern = hasCustomPopup && (options.customClass.popup.includes('swal-ultra-modern') || options.customClass.popup.includes('premium-swal-popup'));
        
        if (!isAlreadyModern && (options.title || options.text) && !options.html) {
            const title = options.title || 'Atención';
            const text = options.text || '';
            const icon = options.icon || 'info'; 
            
            let iconBg, iconColor, bsIcon;
            if (icon === 'warning') { iconBg = '#fee2e2'; iconColor = '#ef4444'; bsIcon = 'bi-exclamation-triangle'; }
            else if (icon === 'error') { iconBg = '#fee2e2'; iconColor = '#ef4444'; bsIcon = 'bi-x-circle'; }
            else if (icon === 'success') { iconBg = '#d1fae5'; iconColor = '#10b981'; bsIcon = 'bi-check-circle'; }
            else if (icon === 'question') { iconBg = '#e0f2fe'; iconColor = '#0ea5e9'; bsIcon = 'bi-question-circle'; }
            else { iconBg = '#f3f4f6'; iconColor = '#6b7280'; bsIcon = 'bi-info-circle'; }

            options.html = `
                <div style="display: flex; gap: 16px; align-items: flex-start; text-align: left;">
                    <div style="flex-shrink: 0; width: 40px; height: 40px; background: ${iconBg}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi ${bsIcon}" style="font-size: 20px; color: ${iconColor};"></i>
                    </div>
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #111827;">${title}</h3>
                        ${text ? `<p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.5;">${text}</p>` : ''}
                    </div>
                </div>
            `;
            
            delete options.title;
            delete options.text;
            delete options.icon;
            
            options.buttonsStyling = false;
            
            const confirmColorClass = (options.confirmButtonColor === '#dc2626' || icon === 'warning' || icon === 'error') 
                                        ? 'swal-btn-danger' : 'swal-btn-primary';
            
            options.customClass = options.customClass || {};
            options.customClass.popup = 'swal-ultra-modern';
            options.customClass.confirmButton = `swal-btn ${confirmColorClass}`;
            options.customClass.cancelButton = 'swal-btn swal-btn-secondary';
            options.customClass.actions = 'swal-actions-right';
        }
        
        return originalSwalFire.call(Swal, options);
    };
}
