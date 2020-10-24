import React from 'react';
import cn from 'classnames';
import PropTypes from 'prop-types';

function Button({ color, classes, size, type, variant, children, ...props }) {
    const className = cn({
        ...classes,
        [`button-color-${color}`]: color,
        [`button-size-${size}`]: size,
        [`button-${variant}`]: variant,
    });

    return (
        <button className={className} type={type} {...props}>
            {children}
        </button>
    );
}

Button.propTypes = {
    color: PropTypes.oneOf(['default', 'primary', 'red', 'green', 'dark', 'light']),
    classes: PropTypes.any,
    size: PropTypes.oneOf(['small', 'medium', 'large']),
    type: PropTypes.oneOf(['button', 'submit', 'reset']),
    variant: PropTypes.oneOf(['text', 'contained', 'outlined', 'link', 'close']),
    children: PropTypes.node,
};

Button.defaultProps = {
    type: 'button',
    variant: 'text',
};

export default Button;
