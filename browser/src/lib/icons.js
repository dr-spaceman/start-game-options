import React from 'react';

import questionblock from '../../images/icons/questionblock.png';
import loadingmascot from '../../images/icons/loading_mascot.gif';

export function QuestionBlock({ className: classNameProp, ...props }) {
    const className = `icon ${classNameProp}`;
    return React.createElement('img', {
        ...props, src: questionblock, alt: '[?]', className,
    });
}

export function LoadingMascot({ className: classNameProp, ...props }) {
    const className = `icon ${classNameProp}`;
    return React.createElement('img', {
        ...props, src: loadingmascot, alt: 'loading', className,
    });
}

export default { QuestionBlock, LoadingMascot };
