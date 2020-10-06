import React from 'react';
import classNames from 'classnames';
import matchComponent from '../lib/match-component.js';
import useOutsideClick from '../lib/use-outside-click.js';

const isDropdownToggle = matchComponent(DropdownToggle);
const isDropdownMenu = matchComponent(DropdownMenu);

function Dropdown(props) {
    const { className, children, ...rest } = props;

    const [open, setOpen] = React.useState(false);
    const handleToggle = () => setOpen(!open);
    const handleClose = () => setOpen(false);

    const classnames = classNames({
        className,
        dropdown: true,
        open,
    });

    // Event listener is always active... problem?
    const wrapperRef = React.useRef(null);
    useOutsideClick(wrapperRef, handleClose);

    return (
        <div ref={wrapperRef} className={classnames} {...rest}>
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

function DropdownToggle({ className, children, handleClick }) {
    return (
        <button className={`dropdown-toggle ${className}`} type="button" onClick={handleClick} aria-haspopup="true" aria-expanded="true">
            {children}
        </button>
    );
}

function DropdownMenu({ className, children }) {
    return (
        <div className={`dropdown-menu light ${className}`} role="menu">
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
