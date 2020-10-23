import React from 'react';
import PropTypes from 'prop-types';
import cn from 'classnames';
import matchComponent from '../lib/match-component.js';

const isNavMenuItem = matchComponent(NavMenuItem);

/**
 * UI component that emulates a videogame select/title screen. A selected item is highlighted with
 * a caret.
 */
function NavMenu({ className, children, ...props }) {
    let initSelected;
    React.Children.forEach(children, (child, index) => {
        if (child.props.selected) {
            initSelected = index;
        }
    });

    // Set to index of <NavMenu.Item> child
    const [selected, setSelected] = React.useState(initSelected);

    const classNames = cn({
        className,
        navmenu: true,
    });

    let firstValidChild;

    return (
        <nav className={classNames} {...props}>
            {React.Children.map(children, (child, index) => {
                if (!React.isValidElement(child)) {
                    return child;
                }

                if (isNavMenuItem(child)) {
                    firstValidChild = firstValidChild || index;
                    return React.cloneElement(child, {
                        index,
                        setSelected,
                        selected: selected === index,
                        tabIndex: firstValidChild === index ? 0 : -1,
                    });
                }

                return child;
            })}
        </nav>
    );
}
NavMenu.propTypes = {
    className: PropTypes.string,
    children: PropTypes.node.isRequired,
};

function NavMenuItem({
    index,
    caret,
    selected,
    setSelected,
    className,
    children,
    ...props
}) {
    const handleClick = (event) => {
        setSelected(index);
    };

    const classNames = cn({
        className,
        'navmenu-item': true,
        selected,
    });

    return (
        <div className={classNames} role="menuitem" onClick={handleClick} aria-hidden="true" {...props}>
            {caret && <div className="navmenu-caret">&gt;&nbsp;</div>}
            <div className="navmenu-item-content">{children}</div>
        </div>
    );
}
NavMenuItem.propTypes = {
    index: PropTypes.number.isRequired,
    /** Prepend a caret on the menu item */
    caret: PropTypes.bool,
    /** Determines if the caret is visible on initial render. */
    selected: PropTypes.bool,
    setSelected: PropTypes.func.isRequired,
    className: PropTypes.string,
    children: PropTypes.node,
};
NavMenuItem.defaultProps = {
    caret: true,
    selected: false,
};

NavMenu.Item = NavMenuItem;

export default NavMenu;
