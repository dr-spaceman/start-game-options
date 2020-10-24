import React from 'react';
import { CSSTransition } from 'react-transition-group';
import NavMenu from './ui/NavMenu.jsx';
import Button from './ui/Button.jsx';

export default function TopNav() {
    const [open, setOpen] = React.useState(false);
    const toggleOpen = () => {
        setOpen(!open);
    };

    const buttonClasses = {
        active: open,
        inactive: !open,
    };

    return (
        <CSSTransition in={open} timeout={1500}>
            <NavMenu>
                <NavMenu.Item selected>
                    <h6><a href="/games">Start Game</a></h6>
                </NavMenu.Item>
                <NavMenu.Item>
                    {/* accessibilize */}
                    <Button role="switch" aria-checked={open} id="menu" classes={buttonClasses} onClick={toggleOpen}>
                        Options
                    </Button>
                </NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/games">Games</a></NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/people">People</a></NavMenu.Item>
                <NavMenu.Item className="hidden"><a href="/music">Music</a></NavMenu.Item>
            </NavMenu>
        </CSSTransition>
    );
}
