import React, { useState, useEffect } from 'react';
import { Box } from '@mui/material';
import { useMediaQuery } from '@mui/material';

const UnderConstructionImage = ({ videoEnded }) => {
  const [showImage, setShowImage] = useState(false);  // Track when to show the image
  const isMobile = useMediaQuery('(max-width:600px)');  // Check if the device is mobile

  // Trigger the Under Construction image after the video ends
  useEffect(() => {
    if (videoEnded) {
      setShowImage(true);
    }
  }, [videoEnded]);

  if (!showImage) return null;  // Don't render if not supposed to be shown

  return (
    <Box
      sx={{
        position: isMobile ? 'absolute' : 'relative', // Overlay on mobile, below on desktop
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        width: '100%',
        height: '100%',
        zIndex: 10,
        backgroundColor: isMobile ? 'rgba(0, 0, 0, 0.5)' : 'transparent',
        maxWidth: isMobile ? 'none' : '100%',
      }}
    >
      <img
        src="/images/website-under-construction.png"
        alt="Website Under Construction"
        style={{
          width: isMobile ? '80%' : '320px',
          height: 'auto',
        }}
      />
    </Box>
  );
};

export default UnderConstructionImage;

