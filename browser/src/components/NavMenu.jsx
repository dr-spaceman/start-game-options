import React from 'react';
import cn from 'classnames';

const Context = React.createContext();

function NavMenu({ className, children, ...props }) {
    const state = React.useState();

    const classNames = cn({
        className,
        navmenu: true,
    });

    return (
        <nav className={classNames} {...props}>
            <Context value={state}>
                {children}
            </Context>
        </nav>
    );
}

function NavMenuItem({
    selected: selectedProp,
    className,
    caret = true,
    children,
    ...props
}) {
    const [selected, setSelected] = React.useContext(Context);
    const handleClick = (event) => {
        setSelected(event.target);
    };

    const classNames = cn({
        className,
        'navmenu-item': true,
        selected: selectedProp && !selected,
    });

    return (
        <div className={classNames} role="menuitem" onClick={handleClick} {...props}>
            {caret && <div className="navmenu-caret">&gt;&nbsp;</div>}
            <div className="navmenu-item-content">{children}</div>
        </div>
    );
}

NavMenu.Item = NavMenuItem;

export default NavMenu;
