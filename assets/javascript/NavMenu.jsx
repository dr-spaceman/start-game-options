import React from 'react';
import { BiMenu, BiPlus } from 'react-icons/bi';
import Modal from './Modal.jsx';

export default function NavMenu(props) {
    const [open, setOpen] = React.useState(false);
    const handleOpen = (event) => {
        event.preventDefault();
        setOpen(true);
    };
    const handleClose = () => {
        setOpen(false);
    };

    return (
        <div id="navmenu">
            <button type="button" id="hamburger" onClick={handleOpen}>Menu</button>
            <Modal open={open} close={handleClose}>
                <ul>
                    <li><a href="/games">Games</a></li>
                    <li><a href="/people">People</a></li>
                    <li><a href="/music">Music</a></li>
                </ul>
            </Modal>
        </div>
    )
}