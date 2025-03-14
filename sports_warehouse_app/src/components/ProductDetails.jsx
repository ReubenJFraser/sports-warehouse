// src/components/ProductDetails.jsx
import React from 'react';
import { Box, Typography } from '@mui/material';

const ProductDetails = ({ product }) => {
  return (
    <Box sx={{ p: 2 }}>
      <Typography variant="h6" gutterBottom>
        {product.name}
      </Typography>
      <Typography variant="body1" paragraph>
        {product.description}
      </Typography>
      <Typography variant="subtitle1">
        Price: {product.price}
      </Typography>
    </Box>
  );
};

export default ProductDetails;
