import React from 'react';
import cn from 'classnames';
import matchComponent from '../../lib/match-component.js';
import useOutsideClick from '../../hooks/use-outside-click.js';

const isDropdownToggle = matchComponent(DropdownToggle);
const isDropdownMenu = matchComponent(DropdownMenu);

function Dropdown({ classes, children, ...props }) {
    const [open, setOpen] = React.useState(false);
    const handleToggle = () => setOpen(!open);
    const handleClose = () => setOpen(false);

    const ref = useOutsideClick(handleClose);

    const className = cn({
        ...classes,
        dropdown: true,
        open,
    });

    return (
        <div ref={ref} className={className} {...props}>
            {/* Map children & inject listeners */}
            {React.Children.map(children, (child) => {
                if (!React.isValidElement(child)) {
                    return child;
                }

                // Button toggle dropdown menu
                if (isDropdownToggle(child)) {
                    return React.cloneElement(child, {
                        handleClick: handleToggle,
                    });
                }

                return child;
            })}
        </div>
    );
}

function DropdownToggle({ classes, children, handleClick }) {
    const className = cn({
        ...classes,
        'dropdown-toggle': true,
    });

    return (
        <button type="button" className={className} onClick={handleClick} aria-haspopup="true" aria-expanded="true">
            {children}
        </button>
    );
}

function DropdownMenu({ classes, children }) {
    const className = cn({
        ...classes,
        'dropdown-menu': true,
        light: true,
    });

    return (
        <div className={className} role="menu">
            {children}
        </div>
    );
}

function DropdownItem({ children }) {
    return (
        <div className="dropdown-item" role="menuitem">{children}</div>
    );
}

Dropdown.Toggle = DropdownToggle;
Dropdown.Menu = DropdownMenu;
Dropdown.Item = DropdownItem;

export default Dropdown;
