import React from 'react';
import classNames from 'classnames';
import OutsideClickHandler from 'react-outside-click-handler';
import matchComponent from '../lib/match-component.js';

const isDropdownToggle = matchComponent(DropdownToggle);

const Dropdown = (props) => {
    const { className, children, ...rest } = props;

    const [open, setOpen] = React.useState(false);
    const handleToggle = () => setOpen(!open);

    const classnames = classNames({
        className,
        dropdown: true,
        open,
    });

    return (
        <OutsideClickHandler onOutsideClick={handleToggle}>
            <div className={classnames} {...rest}>
                {/* Map over children & inject toggle event into button child */}
                {React.Children.map(children, (child) => {
                    if (!React.isValidElement(child)) {
                        return child;
                    }

                    if (isDropdownToggle(child)) {
                        return React.cloneElement(child, {
                            handleClick: handleToggle,
                        });
                    }

                    return child;
                })}
            </div>
        </OutsideClickHandler>
    );
};

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
