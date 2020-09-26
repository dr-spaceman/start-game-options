import React from 'react';
import { IconContext } from 'react-icons';
import { BiMenu, BiX } from 'react-icons/bi';
import Modal from './Modal.jsx';

export default function NavMenu(props) {
    const [open, setOpen] = React.useState(false);
    const handleOpen = (event) => {
        event.preventDefault();
        setOpen(!open);
    };
    const handleClose = () => {
        setOpen(false);
    };

    const classname = open ? 'plain active' : 'plain inactive';

    return (
        <div id="navmenu">
            <button type="button" role="switch" aria-checked={open} id="hamburger" className={classname} onClick={handleOpen}>
                <IconContext.Provider value={{ size: '30px', color: 'white' }}>
                    {open ? <BiX /> : <BiMenu />}
                </IconContext.Provider>
            </button>
            <Modal open={open} close={handleClose}>
                <ul>
                    <li><a href="/games">Games</a></li>
                    <li><a href="/people">People</a></li>
                    <li><a href="/music">Music</a></li>
                </ul>
            </Modal>
        </div>
    );
}
