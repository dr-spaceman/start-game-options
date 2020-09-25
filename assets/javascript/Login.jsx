import React from 'react';

import Modal from './Modal.jsx';
import avatar from '../images/questionblock.png';

export default function Login(props) {
    const { username } = props;

    const form = React.useRef();
    const handleSubmit = (event) => {
        event.preventDefault();
        console.log(form.current.username.value, form.current.password.value);
    };

    const [open, setOpen] = React.useState(false);
    const handleOpen = (event) => {
        event.preventDefault();
        setOpen(true);
    };
    const handleClose = () => {
        setOpen(false);
    };

    const loginButton = (
        <span className="user">
            <a href="/login.php" onClick={handleOpen} style={{ paddingLeft: 18, background: `url(${avatar}) no-repeat left center` }}>
                Login
            </a>
        </span>
    );

    return (
        <>
            {username || loginButton}
            <Modal open={open} close={handleClose}>
                <form ref={form} onSubmit={handleSubmit}>
                    <input type="text" name="username" placeholder="Username" ref={(input) => input && input.focus()} />
                    <input type="password" name="password" placeholder="Password" />
                    <div>
                        <button type="submit">Login</button>
                        <button type="button" onClick={handleClose}>Cancel</button>
                    </div>
                </form>
            </Modal>
        </>
    );
}
