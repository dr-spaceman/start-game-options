import React from 'react';
import cn from 'classnames';

function NavMenu({ className, children, ...props }) {
    const classNames = cn({
        className,
        navmenu: true,
    });

    return (
        <ul className={classNames} {...props}>
            {children}
        </ul>
    );
}

function Item({ selected, className, children, ...props }) {
    const classNames = cn({
        className,
        'navmenu-item': true,
        'navmenu-selected': selected,
    });

    return (
        <li className={classNames} role="menuitem" {...props}>
            <span className="caret">&gt;</span>
            {children}
        </li>
    );
}

NavMenu.Item = Item;

export default NavMenu;
